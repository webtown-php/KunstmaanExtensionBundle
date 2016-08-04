<?php

namespace Webtown\KunstmaanExtensionBundle\Command;

use Kunstmaan\AdminBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webtown\KunstmaanExtensionBundle\User\UserEditService;

class UserEditCommand extends ContainerAwareCommand
{
    const PLEASE_SELECT_A_USER = 'Please select a user';
    /**
     * @var UserEditService
     */
    protected $userEditor;
    /**
     * Max displayable users in multiple choice list
     *
     * @var int
     */
    const MAX_USER_CHOICES = 10;
    /**
     * @var InputInterface
     */
    protected $input;
    /**
     * @var OutputInterface
     */
    protected $output;
    /**
     * @var User[]
     */
    protected $choices;
    /**
     * @var SymfonyStyle
     */
    protected $logger;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('webtown:kunstmaan:user-edit')
            ->setDescription('Edit user details')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL)
            ->addOption('email', 'm', InputOption::VALUE_OPTIONAL);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger = new SymfonyStyle($input, $output);
        $this->input = $input;
        $this->output = $output;
        // user manager service
        $this->userEditor = $this->getContainer()->get('webtown_kunstmaan_extension.user_edit');

        $this->logger->title('User updater');

        // find by options or find all
        $this->choices = $this->userEditor->getChoices($input->getOption('username'), $input->getOption('email'));
        $this->selectionHandler();
    }

    /**
     * Handle user selection depending on options and user count in db
     */
    protected function selectionHandler()
    {
        $userCount = count($this->choices);
        if ($userCount > static::MAX_USER_CHOICES) {
            $this->autocomplete();
        } elseif ($userCount > 1) {
            $this->selector();
        } elseif (1 === $userCount) {
            $this->editor($this->choices[0]);
        }
    }

    /**
     * Multiple choices user select
     *
     * @param User[] $choices
     */
    protected function selector(&$choices = null)
    {
        $question = new ChoiceQuestion(static::PLEASE_SELECT_A_USER, $choices ? $choices : $this->choices);
        $selectedUser = $this->ask($question);
        $user = $this->getChoiceByUsername($selectedUser);
        if ($user) {
            $this->editor($user);
        }
    }

    /**
     * Autocomplete user select
     */
    protected function autocomplete()
    {
        $question = new Question(static::PLEASE_SELECT_A_USER);
        $question->setAutocompleterValues($this->choices);
        $selectedUser = $this->ask($question);
        // nem választott usert, vége
        if ('' === $selectedUser) {
            return;
        }
        $user = $this->getChoiceByUsername($selectedUser);
        // kiválasztott egy usert
        if (!is_null($user)) {
            $this->editor($user);
            // nem választott konkrét usert
        } else {
            $choices = $this->userEditor->getChoices($selectedUser, $selectedUser, true, static::MAX_USER_CHOICES);
            $this->selector($choices);
        }
    }

    /**
     * Show user editor
     *
     * @param User $user
     *
     * @internal param string $username
     */
    protected function editor(User $user)
    {
        $this->logger->section('Editing user' . $user->getUsername() . ' (' . $user->getEmail() . ')');
        $this->logger->comment('leave empty to keep unchanged');
        $username = $this->ask(new Question('Username', $user->getUsername()));
        $email = $this->ask(new Question('E-mail address', $user->getEmail()));
        $password = $this->ask(new Question('Password', '***'));
        $password = $password !== '***' ? $password : '';
        // confirm
        $ln = <<<EOL
Summary
-------
Username: "$username"
Email:    "$email"
Password: "$password"
EOL;
        $this->logger->block($ln);
        $changed =
            $username != $user->getUsername() ||
            $email != $user->getEmail() ||
            $password != '';
        if (!$changed) {
            $this->logger->note('Nothing changed, exiting.');

            return;
        }
        // persist
        if ($this->ask(new ConfirmationQuestion('Confirm user update?'))) {
            $this->userEditor->updateUser($user, $username, $email, $password);
            $this->logger->success('User updated!');
        }
    }

    /**
     * @return mixed
     */
    protected function getQuestionHelper()
    {
        return $this->getHelper('question');
    }

    /**
     * @param string $username
     *
     * @return User
     */
    protected function getChoiceByUsername($username)
    {
        foreach ($this->choices as $item) {
            if ($item->getUsername() === $username) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param Question $question
     *
     * @return string
     */
    protected function ask(Question $question)
    {
        return $this->logger->askQuestion($question);
    }
}

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
use Webtown\KunstmaanExtensionBundle\User\UserUpdater;

class UserEditCommand extends ContainerAwareCommand
{
    /**
     * User select question
     *
     * @var string
     */
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
        $this->userEditor = $this->getContainer()->get('webtown_kunstmaan_extension.user_edit');

        $this->logger->title('User updater');

        // find by options or find all users
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
        $choices = $this->userEditor->getChoicesAsEmailUsername($choices ? $choices : $this->choices);
        $question = new ChoiceQuestion(static::PLEASE_SELECT_A_USER, $choices);
        $selectedUser = $this->ask($question);
        $user = $this->getChoiceBySelection($selectedUser);
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
     */
    protected function editor(User $user)
    {
        // store props
        $oldProps = new UserUpdater($user);
        $newProps = $this->getNewValues($user);

        // confirm
        $ln = <<<EOL
Summary
-------
Username: "{$newProps->getUsername()}"
Email:    "{$newProps->getEmail()}"
Password: "{$newProps->getPassword()}"
EOL;
        $this->logger->block($ln);

        // check changes
        $changedValues = $oldProps->getChanged($newProps);
        if (empty($changedValues)) {
            $this->logger->note('Nothing changed, exiting.');

            return;
        }
        // persist
        if ($persist = $this->ask(new ConfirmationQuestion('Confirm user update?'))) {
            $this->userEditor->updateUser($user, $newProps);
            $this->logger->success('User updated!');
        }
        // send mail
        if ($persist) {
            $dontSend = 'Don\'t send';
            $emailChoices = [$dontSend, $oldProps->getEmail()];
            if (isset($changedValues['email'])) {
                $emailChoices[] = $changedValues['email'];
            }
            $to = $this->ask(new ChoiceQuestion('Send notification to', $emailChoices));
            if ($to !== $dontSend) {
                $this->sendNotification($to, $changedValues);
            }
        }
    }

    /**
     * Send email notification about changes
     *
     * @param       $to
     * @param array $changedValues
     */
    protected function sendNotification($to, array $changedValues)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('User details updated')
            ->setFrom($this->getContainer()->getParameter('fos_user.resetting.email.from_email'))
            ->setTo($to)
            ->setBody(
                $this->getContainer()->get('twig')->render(
                    '@WebtownKunstmaanExtension/email/user_edit.html.twig',
                    ['changes' => $changedValues]
                ),
                'text/html'
            );
        $this->getContainer()->get('mailer')->send($message);
    }

    /**
     * Find User by username
     *
     * @param string $username
     *
     * @return User
     */
    protected function getChoiceBySelection($selection)
    {
        preg_match('/^[^(]+\(([^\)]+)\)$/', $selection, $matches);
        if (count($matches) < 2) {
            return;
        }
        $username = $matches[1];
        foreach ($this->choices as $item) {
            if ($item->getUsername() === $username) {
                return $item;
            }
        }

        return;
    }

    /**
     * Find User by username
     *
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

        return;
    }

    /**
     * Ask question
     *
     * @param Question $question
     *
     * @return string
     */
    protected function ask(Question $question)
    {
        return $this->logger->askQuestion($question);
    }

    /**
     * Ask for new user properties
     *
     * @param User $user
     *
     * @return UserUpdater
     */
    protected function getNewValues(User $user)
    {
        $newProps = new UserUpdater();
        $this->logger->section('Editing user' . $user->getUsername() . ' (' . $user->getEmail() . ')');
        $this->logger->comment('leave empty to keep unchanged');
        $newProps->setUsername($this->ask(new Question('Username', $user->getUsername())));
        $newProps->setEmail($this->ask(new Question('E-mail address', $user->getEmail())));
        $password = $this->ask(new Question('Password', '***'));
        // replace default *** value if empty is given
        $newProps->setPassword($password !== '***' ? $password : '');

        return $newProps;
    }
}

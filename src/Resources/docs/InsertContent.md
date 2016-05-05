# Insert Content

There is the `Webtown\KunstmaanExtensionBundle\Entity\PageParts\InsertPagePagePart` Kunstmaan Page Part. Set it in the `main.yml` or in the other page settings yml file:

```yml
name: Main
context: main
types:
    - { name: Audio, class: BssOil\PublicBundle\Entity\PageParts\AudioPagePart }
    # [...]
    - { name: Insert page, class: Webtown\KunstmaanExtensionBundle\Entity\PageParts\InsertPagePagePart }
```

Now you can insert a page into the other page.

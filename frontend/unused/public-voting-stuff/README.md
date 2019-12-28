okay so im just gonna put a file in here to help myself stay sane while trying to figure out how the old site worked cool? cool.

basically there was one page for every category of categories - genres, characters, production, main, etc. and every one of those pages had its entry data injected through the ERB page serving it. every page contained a

```html
<script>
showsJSON = <%= (some json string) %>
</script>
```

followed by the script to display the page, which would then read from `showsJSON` to get its contents.

this is obviously not an okay way to do things since we have a data store for all that shit.

so this time we need to do it a bit better, and we'll do that by reading entry data through vuex instead. that's the migration that i have to do after all this shit is converted to a webpackable format and all that. wheeeee

also remember that the backend will have to handle the votes in the database and stuff so that's neat

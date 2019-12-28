# Component Organization

The way different categories are switching between components on the fly and the conditions that handle them are borderline insane. So in order for people to not go insane trying to figure this out, I'm gonna make an attempt at documenting what I did. Note that there's a JS file exporting a bunch of anilist queries right outside this folder and the show query needs to be modified with the right eligibility period since the one there rn isn't right.

- **Genre Cats** pull entries from the host dashboard. Make sure you have a genre cat in the dashboard with some entries before testing this tab group. Panda's "select all" is convenient for mercilessly shoving shows into a cat. The component for these cats is `DashboardPicker`.
- **Chara Cats** pull entries from the AniList API. The problem is that there's no way to apply end date filtering in the query itself. But since the end date can be returned by the API, it's easy to filter that on the client-side. The locks between these categories however will be an absolute pain to handle. The component for these cats is `CharPicker`.
- **Main Cats** are divided into two categories. Categories that are named "Anime of the Year" and categories that aren't. All categories that aren't named "Anime of the Year" will pull entries from the host dashboard and will thus use the `DashboardPicker` component. Categories named "Anime of the Year" will use an entirely different component called `ShowPicker` which essentially sends a complicated AniList query that returns shows within the eligibility period if they satisfy Awards Criteria for TV anime. Note that the query will have to be modified with the correct eligibility criteria once it becomes clear but it's very safe to use `AWARDSYEAR0000-AWARDSYEAR9999` until you've figured it out for testing purposes.
- **Test Cats** are once again conditional. Any category with "Sports" (The 'S' must be uppercase always) in the name will use the `DashboardPicker` component. As for all other test categories, they will use the `TestPicker` component, a modified version of the `ShowPicker` component which just removes the duration and episode filters from the query to return all TV anime, movies, OVAs etc.
- **Production Categories** are another fun one. They have 3 different entry types so  this was gonna be accordingly complicated. If their entry type is `vas`, they use the `VAPicker` component, if it's `themes` they use the `MusicPicker` component. For `MusicPicker`, we'll pass a prop for the component to filter between OP/ED/OST. And if they're any other kind of entry type, they use `ShowPicker`, the AotY component.

To put it another way:

- **DashboardPicker** is all genre cats, all main awards cats that aren't AotY and the Sports test cat.
- **CharPicker** is all character cats.
- **ShowPicker** is AotY and all production cats with entryType `shows`.
- **VAPicker** is cats with entryType `vas`.
- **MusicPicker** is cats with entryType `themes`.
- **TestPicker** is test categories that aren't Sports.

I hope I haven't coded up a nightmare.

# Host Instructions for Dashboard

There's a few categories that need to be filled up in the dashboard and a few categories that are pulled from AniList directly.

- **Genre cats** pull from the dashboard so you'll have to enter all eligible shows into their right genre cat.
- **Chara cats** pull from AniList so leave them untouched.
- **Main Awards** pull from the dashboard except AotY. Fill up all of these except AotY.
- **Test cats** pull from the dashboard for Sports and AniList for the rest. Only fill up sports.
- **Production cats** pull from AniList while themes pull from the CSV sheet. No need to fill these up.
# Component Organization

The way different categories are switching between components on the fly and the conditions that handle them are borderline insane. So in order for people to not go insane trying to figure this out, I'm gonna make an attempt at documenting what I did. Note that there's a JS file exporting a bunch of anilist queries right outside this folder and the show query needs to be modified with the right eligibility period since the one there rn isn't right.

- **DashboardPicker** is all genre cats, all main awards cats that aren't AotY and the Sports test cat.
- **CharPicker** is all character cats except cast.
- **ShowPicker** is AotY and all production cats with entryType `shows`, also Cast and OST because shit's fucked up.
- **VAPicker** is cats with entryType `vas`.
- **MusicPicker** is cats with entryType `themes` except OST.
- **TestPicker** is test categories that aren't Sports.

I hope I haven't coded up a nightmare.

# Host Instructions for Dashboard

There's a few categories that need to be filled up in the dashboard and a few categories that are pulled from AniList directly.

- **Genre cats** pull from the dashboard so you'll have to enter all eligible shows into their right genre cat.
- **Chara cats** pull from AniList so leave them untouched.
- **Main Awards** pull from the dashboard except AotY. Fill up all of these except AotY.
- **Test cats** pull from the dashboard for Sports and AniList for the rest. Only fill up sports.
- **Production cats** pull from AniList while themes pull from the CSV sheet. OST needs to be filled up as it pulls from the dashboard.

DO NOT TOUCH THE BEST OST CATEGORY AT ALL.
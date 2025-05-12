<html data-theme="dark">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="og:title" content="/r/anime Awards 2024">
        <meta name="description" content="Welcome to the 2024 r/Anime Awards! This site contains all of the info about the winners and rankings for the award categories featured this year.">
        <meta name="og:type" content="website">
        <meta name="og:image" content="https://animeawards.moe/img/snoo_kenichiroaoki.png">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:description" content="Reddit Anime's 2024 Awards!">
        <meta name="twitter:image" content="https://animeawards.moe/img/snoo_kenichiroaoki.png">
        <meta name="og:description" content="Welcome to the 2024 r/Anime Awards! This site contains all of the info about the winners and rankings for the award categories featured this year.">
        <meta name="og:url" content="https://animeawards.moe">
        <title>{{ $title ?? 'r/anime awards' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="">
        <div class="hero is-fullheight max-width">
            <nav class="navbar" role="navigation" aria-label="main navigation">
                <div class="navbar-brand">
                    <a class="navbar-item" href="https://bulma.io">
                        <img src="{{URL::asset('/images/awards2024.png')}}" alt="profile Pic">
                    </a>
                    <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
                        <span aria-hidden="true"></span>
                        <span aria-hidden="true"></span>
                        <span aria-hidden="true"></span>
                        <span aria-hidden="true"></span>
                    </a>
                </div>
                <div id="navbarBasicExample" class="navbar-menu">
                    <div class="navbar-start">
                    <a class="navbar-item">
                        Home
                    </a>
                    <a class="navbar-item">
                        Documentation
                    </a>
                    <div class="navbar-item has-dropdown is-hoverable">
                        <a class="navbar-link">
                        More
                        </a>
                        <div class="navbar-dropdown">
                        <a class="navbar-item">
                            About
                        </a>
                        <a class="navbar-item is-selected">
                            Jobs
                        </a>
                        <a class="navbar-item">
                            Contact
                        </a>
                        <hr class="navbar-divider">
                        <a class="navbar-item">
                            Report an issue
                        </a>
                        </div>
                    </div>
                    </div>

                    <div class="navbar-end">
                        <div class="navbar-item">
                            <div class="buttons">
                            <a class="button is-primary">
                                <strong>Sign up</strong>
                            </a>
                            <a class="button is-light">
                                Log in
                            </a>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
            <section id="mainContent" class="container">
                {{ $slot }}
            </section>
            <footer class="footer mt-auto">
                <div class="content has-text-centered">
                    <p>
                        The r/anime Awards are a rag tag group of volunteers passionate about showcasing the shows we love. As such, we do not own or claim ownership of any of the shows, characters, or images showcased on this site. All copyrights and/or trademarks of any character and/or image used belong to their respective owner(s). Please don't sue us. 
                    </p>
                </div>
            </footer>
        </div>
    </body>

</html>
<!DOCTYPE html>
<html data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="og:title" content="/r/anime Awards 2025">
    <meta name="description" content="Welcome to the 2025 r/Anime Awards! This site contains all of the info about the winners and rankings for the award categories featured this year.">
    <meta name="og:type" content="website">
    <meta name="og:image" content="https://animeawards.moe/img/snoo_kenichiroaoki.png">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:description" content="Reddit Anime's 2025 Awards!">
    <meta name="twitter:image" content="https://animeawards.moe/images/snoo_kenichiroaoki.png">
    <meta name="og:description" content="Welcome to the 2025 r/Anime Awards! This site contains all of the info about the winners and rankings for the award categories featured this year.">
    <meta name="og:url" content="https://animeawards.moe">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'r/anime awards' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/pubjury.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        /* Make navbar sticky */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        /* Style the feedback link */
        .navbar .navbar-item {
            color: white;
            transition: all 0.3s ease;
            border-radius: 4px;
            padding: 0.5rem 1rem;
        }
        
        .navbar .navbar-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffdd57;
        }
        
        /* Remove highlighting from anchor tags inside navbar-item */
        .navbar .navbar-item a {
            color: inherit;
            text-decoration: none;
        }
        
        .navbar .navbar-item a:hover {
            background: none;
            color: inherit;
        }
        
        /* Burger menu styling */
        .navbar-burger {
            color: white;
        }
        
        .navbar-burger:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        /* Mobile menu styling */
        @media screen and (max-width: 1023px) {
            .navbar-menu {
                background: rgba(0, 0, 0, 0.8);
                backdrop-filter: blur(10px);
            }
        }
    </style>
</head>
<body>
    <!-- Canvas Background -->
    <canvas id="backgroundCanvas" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; opacity: 0.8;"></canvas>
    
    <div class="hero is-fullheight max-width" style="position: relative; z-index: 1;">
        <nav class="navbar" role="navigation" aria-label="main navigation" style="background: rgba(0, 0, 0, 0.3); backdrop-filter: blur(10px);">
            <div class="navbar-brand">
                <!-- Logo -->
                <a class="navbar-item" href="/">
                    <img src="{{ URL::asset('/images/awardslogo.png') }}" alt="r/anime Awards Logo" style="max-height: 3rem; height: auto; width: auto;">
                </a>
                
                <!-- Burger menu for mobile -->
                <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarMenu">
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                </a>
            </div>
            
            <!-- Navigation menu -->
            <div id="navbarMenu" class="navbar-menu">
                <div class="navbar-start">
                    <!-- Future navigation items will go here -->
                </div>
                
                <div class="navbar-end">
                    <div class="navbar-item">
                        <a class="navbar-item" href="/feedback">
                            <span>Feedback</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        <section id="mainContent" class="container-fluid">
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
<script>
    (function() {
        var canvas, ctx, circ, nodes, mouse, SENSITIVITY, SIBLINGS_LIMIT, DENSITY, NODES_QTY, ANCHOR_LENGTH, MOUSE_RADIUS;

        // how close next node must be to activate connection (in px)
        // shorter distance == better connection (line width)
        SENSITIVITY = 150;
        // note that siblings limit is not 'accurate' as the node can actually have more connections than this value
        // that's because the node accepts sibling nodes with no regard to their current connections
        // this is acceptable because potential fix would not result in significant visual difference
        // more siblings == bigger node
        SIBLINGS_LIMIT = 6;
        // default node margin
        DENSITY = 100;
        // total number of nodes used (incremented after creation)
        NODES_QTY = 0;
        // avoid nodes spreading
        ANCHOR_LENGTH = 400;
        // highlight radius
        MOUSE_RADIUS = 500;

        circ = 2 * Math.PI;
        nodes = [];

        canvas = document.querySelector('#backgroundCanvas');

        resizeWindow();
        mouse = {
            x: canvas.width / 2,
            y: canvas.height / 2,
            vx: 1,
            vy: 1,
        };

        ctx = canvas.getContext('2d');
        if (!ctx) {
            alert("Ooops! Your browser does not support canvas :'(");
        }

        function Node(x, y) {
            this.anchorX = x;
            this.anchorY = y;
            this.x = Math.random() * (x - (x - ANCHOR_LENGTH)) + (x - ANCHOR_LENGTH);
            this.y = Math.random() * (y - (y - ANCHOR_LENGTH)) + (y - ANCHOR_LENGTH);
            this.vx = Math.random() * 2 - 1;
            this.vy = Math.random() * 2 - 1;
            this.energy = Math.random() * 100;
            this.radius = Math.random();
            this.siblings = [];
            this.brightness = 0;
        }

        Node.prototype.drawNode = function() {
            var color = "rgba(231, 169, 36, " + this.brightness + ")";
            ctx.beginPath();
            ctx.arc(this.x, this.y, 2 * this.radius + 2 * this.siblings.length / SIBLINGS_LIMIT, 0, circ);
            ctx.fillStyle = color;
            ctx.fill();
        };

        Node.prototype.drawConnections = function() {
            for (var i = 0; i < this.siblings.length; i++) {
                var color = "rgba(231, 169, 36, " + this.brightness + ")";
                ctx.beginPath();
                ctx.moveTo(this.x, this.y);
                ctx.lineTo(this.siblings[i].x, this.siblings[i].y);
                ctx.lineWidth = 1 - calcDistance(this, this.siblings[i]) / SENSITIVITY;
                ctx.strokeStyle = color;
                ctx.stroke();
            }
        };

        Node.prototype.moveNode = function() {
            this.energy -= 2;
            if (this.energy < 1) {
                this.energy = Math.random() * 800;
                if (this.x - this.anchorX < -ANCHOR_LENGTH) {
                    this.vx = Math.random() * 0.5;
                } else if (this.x - this.anchorX > ANCHOR_LENGTH) {
                    this.vx = Math.random() * -0.5;
                } else {
                    this.vx = Math.random() * 1 - 0.5;
                }
                if (this.y - this.anchorY < -ANCHOR_LENGTH) {
                    this.vy = Math.random() * 0.5;
                } else if (this.y - this.anchorY > ANCHOR_LENGTH) {
                    this.vy = Math.random() * -0.5;
                } else {
                    this.vy = Math.random() * 1 - 0.5;
                }
            }
            this.x += this.vx * this.energy / 800;
            this.y += this.vy * this.energy / 800;
        };

        function initNodes() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            nodes = [];
            for (var i = 0; i < canvas.width + DENSITY * 2; i += DENSITY) {
                for (var j = 0; j < canvas.height + DENSITY * 2; j += DENSITY) {
                    nodes.push(new Node(i, j));
                    NODES_QTY++;
                }
            }
        }

        function calcDistance(node1, node2) {
            return Math.sqrt(Math.pow(node1.x - node2.x, 2) + (Math.pow(node1.y - node2.y, 2)));
        }

        function findSiblings() {
            var node1, node2, distance;
            for (var i = 0; i < NODES_QTY; i++) {
                node1 = nodes[i];
                node1.siblings = [];
                for (var j = 0; j < NODES_QTY; j++) {
                    node2 = nodes[j];
                    if (node1 !== node2) {
                        distance = calcDistance(node1, node2);
                        if (distance < SENSITIVITY) {
                            if (node1.siblings.length < SIBLINGS_LIMIT) {
                                node1.siblings.push(node2);
                            } else {
                                var node_sibling_distance = 0;
                                var max_distance = 0;
                                var s;
                                for (var k = 0; k < SIBLINGS_LIMIT; k++) {
                                    node_sibling_distance = calcDistance(node1, node1.siblings[k]);
                                    if (node_sibling_distance > max_distance) {
                                        max_distance = node_sibling_distance;
                                        s = k;
                                    }
                                }
                                if (distance < max_distance) {
                                    node1.siblings.splice(s, 1);
                                    node1.siblings.push(node2);
                                }
                            }
                        }
                    }
                }
            }
        }

        function redrawScene() {
            resizeWindow();
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            findSiblings();
            var i, node, distance;
            for (i = 0; i < NODES_QTY; i++) {
                node = nodes[i];
                distance = calcDistance({
                    x: mouse.x,
                    y: mouse.y
                }, node);
                if (distance < MOUSE_RADIUS) {
                    node.brightness = 1 - distance / MOUSE_RADIUS;
                } else {
                    node.brightness = 0;
                }
            }
            for (i = 0; i < NODES_QTY; i++) {
                node = nodes[i];
                if (node.brightness) {
                    // node.drawNode();
                    node.drawConnections();
                }
                node.moveNode();
            }

            mouse.x += mouse.vx;
            mouse.y += mouse.vy;

            if (mouse.x < 0 || mouse.x > canvas.width) {
                mouse.vx *= -1;
            }
            if (mouse.y < 0 || mouse.y > canvas.height) {
                mouse.vy *= -1;
            }

            requestAnimationFrame(redrawScene);
        }

        function initHandlers() {
            document.addEventListener('resize', resizeWindow, false);
            // document.addEventListener('mousemove', mousemoveHandler, false);
        }

        function resizeWindow() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }

        function mousemoveHandler(e) {
            mouse.x = e.clientX;
            mouse.y = e.clientY;
        }

        initHandlers();
        initNodes();
        redrawScene();
    })();

    // Navbar burger menu script
    document.addEventListener('DOMContentLoaded', () => {
        // Get all "navbar-burger" elements
        const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);

        // Add a click event on each of them
        $navbarBurgers.forEach(el => {
            el.addEventListener('click', () => {
                // Get the target from the "data-target" attribute
                const target = el.dataset.target;
                const $target = document.getElementById(target);

                // Toggle the "is-active" class on both the "navbar-burger" and the "navbar-menu"
                el.classList.toggle('is-active');
                $target.classList.toggle('is-active');
            });
        });
        
        // Check for redirect URL after login (for dashboard pages and application pages)
        const redirectUrl = sessionStorage.getItem('redirectAfterLogin');
        if (redirectUrl && (window.location.pathname.includes('/dashboard') || window.location.pathname.includes('/participate'))) {
            // Send the redirect URL to the server
            fetch('{{ route("application.set-redirect-url") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ redirect_url: redirectUrl })
            }).then(() => {
                sessionStorage.removeItem('redirectAfterLogin');
                // Redirect to the intended page
                window.location.href = redirectUrl;
            }).catch(() => {
                // If there's an error, just remove the stored URL
                sessionStorage.removeItem('redirectAfterLogin');
            });
        }
    });
</script>

</html>
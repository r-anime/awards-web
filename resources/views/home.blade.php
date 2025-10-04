<x-layout title="Home - r/anime Awards"> 
    <div class="homepage-container">
        <div class="columns is-vcentered is-fullheight ">
            <div class="column is-8 mx-4">
                <div class="welcome-content">
                    <h1 class="welcome-text has-text-white">
                        Welcome to the
                    </h1>
                    <div class="logo-container">
                        <img src="/images/awardslogo.png" alt="r/animeawards logo" class="awards-logo">
                    </div>
                    <p class="subtitle is-4 has-text-white">
                    The r/anime awards are an annual event for the <a href="https://www.reddit.com/r/anime/">r/anime</a> subreddit, completely run by and for the community. Our panels of jurors are required to watch each nominee to completion and our results are split into a public and jury ranking to highlight the best the year has to offer. 
                    </p>
                    
                    @php
                        $currentApplication = \App\Models\Application::where('start_time', '<=', now())
                            ->where('end_time', '>=', now())
                            ->first();
                    @endphp

                    @if($currentApplication)
                        <div class="notification is-success is-light mt-4">
                            <div class="level">
                                <div class="level-left">
                                    <div class="level-item">
                                        <span class="icon">
                                            <i class="fas fa-clipboard-list"></i>
                                        </span>
                                        <span class="has-text-weight-semibold">Applications Open!</span>
                                    </div>
                                </div>
                                <div class="level-right">
                                    <div class="level-item">
                                        <a href="/participate/application" class="button is-primary is-medium">
                                            <span class="icon">
                                                <i class="fas fa-edit"></i>
                                            </span>
                                            <span>Apply Now</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="content mt-3">
                                <p class="has-text-grey">
                                    Applications are currently open for the {{ $currentApplication->year }} awards. 
                                    The application period ends on {{ \Carbon\Carbon::parse($currentApplication->end_time)->format('F j, Y \a\t g:i A') }}.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="notification is-warning is-light mt-4">
                            <div class="level">
                                <div class="level-left">
                                    <div class="level-item">
                                        <span class="icon">
                                            <i class="fas fa-tools"></i>
                                        </span>
                                        <span class="has-text-weight-semibold">Under Construction</span>
                                    </div>
                                </div>
                                <div class="level-right">
                                    <div class="level-item">
                                        <span class="has-text-grey">We're currently in the process of migrating our archive data of past results. Please bear with us as we move to a new system.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="column is-4">
                <div class="snoo-container">
                    <img src="/images/snoo_hyeinlee.png" alt="Snoo" class="snoo-image">
                </div>
            </div>
        </div>
        
        <div class="livestream-container">
            <div class="livestream-wrapper">
                <iframe 
                    src="https://www.youtube.com/embed/fsQRgT3Kuao"
                    frameborder="0"
                    allowfullscreen="true">
                </iframe>
            </div>
        </div>
        
    </div>

    <style>
        .is-fullheight {
            min-height: 100vh;
        }

        .homepage-container {
            position: relative;
            overflow: hidden;
            width: 100%;
        }
        
        .welcome-content {
            padding: 2rem;
            text-align: left;
        }
        
        .welcome-text {
            font-size: 4rem;
            font-weight: 600;
            margin-bottom: 2rem;
            line-height: 1.2;
        }
        
        .logo-container {
            margin-bottom: 3rem;
        }
        
        .awards-logo {
            max-width: 100%;
            height: auto;
            max-height: 200px;
        }
        
        .snoo-container {
            position: relative;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-right: 2rem;
        }
        
        .snoo-image {
            max-width: 100%;
            height: auto;
            max-height: 80vh;
            object-fit: contain;
            position: relative;
            z-index: 1;
        }
        
        .livestream-container {
            height: 100vh;
            padding: 0;
            background-color: #0e0e10;
            margin: 0;
        }
        
        .livestream-wrapper {
            position: relative;
            width: 100vw;
            height: 100vh;
            margin-left: calc(-50vw + 50%);
            border-radius: 0;
            overflow: hidden;
            box-shadow: none;
        }
        
        .livestream-wrapper iframe {
            border-radius: 0;
            width: 100%;
            height: 100%;
        }
        
        @media (max-width: 768px) {
            .welcome-text {
                font-size: 2.5rem;
            }
            
            .snoo-container {
                height: 50vh;
                padding-right: 1rem;
            }
            
            .snoo-image {
                max-height: 40vh;
            }
            
            .livestream-wrapper {
                height: 100vh;
            }
            
            .livestream-container {
                padding: 0;
            }
        }
        
        @media (max-width: 480px) {
            .welcome-text {
                font-size: 2rem;
            }
            
            .livestream-wrapper {
                height: 100vh;
            }
        }
        
        /* Application button styling */
        .notification.is-success .button.is-primary {
            background-color: #ffdd57;
            color: #000;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
        }
        
        .notification.is-success .button.is-primary:hover {
            background-color: #ffeb3b;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 221, 87, 0.4);
        }
        
        .notification.is-success .button.is-primary:focus {
            box-shadow: 0 0 0 3px rgba(255, 221, 87, 0.3);
        }
        
        .notification.is-success {
            background: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        @media (max-width: 768px) {
            .notification .level {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .notification .level-right {
                margin-top: 1rem;
                width: 100%;
            }
            
            .notification .level-right .button {
                width: 100%;
            }
        }
    </style>
</x-layout>
<x-layout title="Sign In - r/anime Awards">
    <div class="columns is-centered is-align-items-center mt-6">
        <div class="column is-half is-flex is-align-items-center is-justify-content-center is-fullheight">
            <div class="box has-text-centered" style="width: 100%;">
                <div class="has-text-centered m-6">
                    <img src="{{ asset('images/awardslogo.png') }}" alt="r/anime Awards Logo" style="max-height: 4rem; height: auto; width: auto;">
                </div>
                
                <!-- Reddit OAuth Button Container -->
                <div class="reddit-oauth-container">
                    <a href="{{ route('socialite.filament.admin.oauth.redirect', ['provider' => 'reddit']) }}{{ request()->has('redirect') ? '?redirect=' . urlencode(request()->input('redirect')) : '' }}" 
                        class="button reddit-signin-button">
                        <div class="reddit-button-content">
                            <svg class="reddit-icon" viewBox="0 0 24 24" width="24" height="24">
                                <path fill="currentColor" d="M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 0 1-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.01 1.614a3.111 3.111 0 0 1 .042.52c0 2.694-3.13 4.87-7.004 4.87-3.874 0-7.004-2.176-7.004-4.87 0-.183.015-.366.043-.534A1.748 1.748 0 0 1 4.028 12c0-.968.786-1.754 1.754-1.754.463 0 .898.196 1.207.49 1.207-.883 2.878-1.43 4.744-1.487l.885-4.182a.342.342 0 0 1 .14-.197.35.35 0 0 1 .238-.042l2.906.617a1.214 1.214 0 0 1 1.108-.701zM9.25 12C8.561 12 8 12.562 8 13.25c0 .687.561 1.248 1.25 1.248.687 0 1.248-.561 1.248-1.249 0-.688-.561-1.249-1.249-1.249zm5.5 0c-.687 0-1.248.561-1.248 1.25 0 .687.561 1.248 1.249 1.248.688 0 1.249-.561 1.249-1.249 0-.687-.562-1.249-1.25-1.249zm-5.466 3.99a.327.327 0 0 0-.231.094.33.33 0 0 0 0 .463c.842.842 2.484.913 2.961.913.477 0 2.105-.056 2.961-.913a.361.361 0 0 0 .029-.463.33.33 0 0 0-.464 0c-.547.533-1.684.73-2.512.73-.828 0-1.979-.196-2.512-.73a.326.326 0 0 0-.232-.095z"/>
                            </svg>
                            <span class="reddit-button-text">Sign in with Reddit</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Full height layout for vertical centering */
            .is-fullheight {
                min-height: 100%;
            }

            .reddit-oauth-container {
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 2rem 0;
            }

            .reddit-signin-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: #ff4500;
                border: 1px solid #ff4500;
                color: white;
                font-weight: 600;
                font-size: 1rem;
                padding: 0.75rem 2rem;
                border-radius: 0.5rem;
                transition: all 0.2s ease;
                text-decoration: none;
                box-shadow: 0 2px 8px rgba(255, 69, 0, 0.2);
                text-transform: none;
                letter-spacing: 0.025em;
                position: relative;
                min-width: 240px;
                cursor: pointer;
            }

            .reddit-signin-button:hover {
                background: #e03e00;
                border-color: #e03e00;
                box-shadow: 0 4px 12px rgba(255, 69, 0, 0.3);
                color: white;
                text-decoration: none;
            }

            .reddit-signin-button:active {
                transform: translateY(1px);
                box-shadow: 0 2px 6px rgba(255, 69, 0, 0.2);
            }

            .reddit-button-content {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                position: relative;
                z-index: 1;
            }

            .reddit-icon {
                width: 24px;
                height: 24px;
                flex-shrink: 0;
            }

            .reddit-button-text {
                font-size: 1.1rem;
                font-weight: 700;
                letter-spacing: 0.05em;
            }
        </style>
    @endpush
</x-layout>

<x-layout title="Page Not Found - r/anime Awards">
    <div class="section">
        <div class="container">
            <div class="has-text-centered">
                <h1 class="title is-2 has-text-white mb-4">
                    Page Not Found
                </h1>
                <p class="subtitle is-4 has-text-white mb-6">
                    The page you're looking for doesn't exist.
                </p>
                <div class="content has-text-white">
                    <p class="mb-4">
                        This might be because the page was moved, deleted, or you entered the wrong URL.
                    </p>
                    <p class="mb-6">
                        Let's get you back on track!
                    </p>
                    <div class="buttons is-centered">
                        <a href="{{ url('/') }}" class="button is-primary is-large">
                            <span class="icon">
                                <i class="fas fa-home"></i>
                            </span>
                            <span>Go Home</span>
                        </a>
                        <button onclick="history.back()" class="button is-info is-large">
                            <span class="icon">
                                <i class="fas fa-arrow-left"></i>
                            </span>
                            <span>Go Back</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>

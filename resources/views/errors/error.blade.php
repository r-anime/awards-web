<x-layout title="Error - r/anime Awards">
    <div class="section">
        <div class="container">
            <div class="has-text-centered">
                <h1 class="title is-2 has-text-white mb-4">
                    @if(isset($exception) && $exception->getCode())
                        Error {{ $exception->getCode() }}
                    @else
                        Something went wrong
                    @endif
                </h1>
                <p class="subtitle is-4 has-text-white mb-6">
                    We're sorry, but something unexpected happened.
                </p>
                <div class="content has-text-white">
                    <p class="mb-4">
                        Don't worry, our team has been notified and we're working to fix this issue.
                    </p>
                    <p class="mb-6">
                        In the meantime, you can try refreshing the page or going back to the homepage.
                    </p>
                    <div class="buttons is-centered">
                        <a href="{{ url('/') }}" class="button is-primary is-large">
                            <span class="icon">
                                <i class="fas fa-home"></i>
                            </span>
                            <span>Go Home</span>
                        </a>
                        <button onclick="window.location.reload()" class="button is-info is-large">
                            <span class="icon">
                                <i class="fas fa-refresh"></i>
                            </span>
                            <span>Try Again</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>

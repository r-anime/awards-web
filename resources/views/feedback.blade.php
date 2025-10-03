<x-layout title="Feedback - r/anime Awards">
    <div class="container is-max-widescreen my-6">
        <div class="columns is-centered">
            <div class="column is-half">
                <div class="card">
                    <div class="card-header">
                        <div class="card-header-title">
                            <h1 class="title is-3 has-text-white">
                                <span class="icon">
                                    <i class="fas fa-comment-dots"></i>
                                </span>
                                Feedback Form
                            </h1>
                        </div>
                    </div>
                    
                    <div class="card-content">
                        <p class="subtitle is-6 has-text-grey-light mb-5">
                            Let us know if you have any problems or suggestions with the site.
                        </p>

                        @if(session('success'))
                            <div class="notification is-success">
                                <button class="delete"></button>
                                <strong>Success!</strong> {{ session('success') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="notification is-danger">
                                <button class="delete"></button>
                                <strong>Please fix the following errors:</strong>
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('feedback.store') }}">
                            @csrf
                            
                            <div class="field">
                                <label class="label has-text-white">Your Name</label>
                                <div class="control">
                                    <input 
                                        class="input @error('name') is-danger @enderror" 
                                        type="text" 
                                        name="name" 
                                        value="{{ old('name') }}"
                                        placeholder="Name"
                                        required
                                    >
                                </div>
                                @error('name')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field">
                                <label class="label has-text-white">Your Message</label>
                                <div class="control">
                                    <textarea 
                                        class="textarea @error('message') is-danger @enderror" 
                                        name="message" 
                                        rows="6" 
                                        maxlength="2000"
                                        placeholder="Share your feedback, suggestions, or report issues..."
                                        required
                                    >{{ old('message') }}</textarea>
                                </div>
                                <div class="is-flex is-justify-content-space-between is-align-items-center">
                                    <p class="help">
                                        Maximum 2000 characters. Be constructive and respectful.
                                    </p>
                                    <span class="tag" id="char-count">0/2000</span>
                                </div>
                                @error('message')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="notification is-warning mt-4">
                                <div class="media">
                                    <div class="media-content">
                                        <p class="title is-6">Rate Limiting</p>
                                        <p class="subtitle is-6">You can submit feedback up to 5 times per week to prevent spam.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="field is-grouped is-grouped-right">
                                <div class="control">
                                    <button type="submit" class="button is-primary is-medium">
                                        <span>Submit Feedback</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .card-header {
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .input, .textarea {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .input::placeholder, .textarea::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .input:focus, .textarea:focus {
            border-color: #ffdd57;
            box-shadow: 0 0 0 0.125em rgba(255, 221, 87, 0.25);
        }
        
        .button.is-primary {
            background-color: #ffdd57;
            color: #000;
            font-weight: 600;
        }
        
        .button.is-primary:hover {
            background-color: #ffeb3b;
        }
        
        .tag {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .notification {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .notification.is-success {
            background: rgba(40, 167, 69, 0.2);
            border-color: rgba(40, 167, 69, 0.3);
        }
        
        .notification.is-danger {
            background: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.3);
        }
        
        .notification.is-warning {
            background: rgba(255, 193, 7, 0.2);
            border-color: rgba(255, 193, 7, 0.3);
        }
    </style>

    <script>
        // Character counter
        document.addEventListener('DOMContentLoaded', function() {
            const messageTextarea = document.querySelector('textarea[name="message"]');
            const charCount = document.getElementById('char-count');
            
            function updateCharCount() {
                const length = messageTextarea.value.length;
                charCount.textContent = length + '/2000';
                
                if (length > 1800) {
                    charCount.classList.add('is-warning');
                } else {
                    charCount.classList.remove('is-warning');
                }
                
                if (length >= 2000) {
                    charCount.classList.add('is-danger');
                } else {
                    charCount.classList.remove('is-danger');
                }
            }
            
            messageTextarea.addEventListener('input', updateCharCount);
            updateCharCount(); // Initial count
            
            // Auto-dismiss notifications
            document.querySelectorAll('.notification .delete').forEach(deleteBtn => {
                deleteBtn.addEventListener('click', function() {
                    this.parentElement.remove();
                });
            });
        });
    </script>
</x-layout>

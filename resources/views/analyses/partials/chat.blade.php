<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900" x-data="chatComponent()">
        <h3 class="text-lg font-semibold mb-4">{{ __('Assistant RH') }}</h3>

        <div class="space-y-4">
            <div class="border border-gray-200 rounded-lg p-4 h-80 overflow-y-auto space-y-3" x-ref="messagesContainer">
                <template x-for="msg in messages" :key="msg.id">
                    <div>
                        <div class="flex" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                            <div class="max-w-[80%] rounded-lg px-4 py-2 text-sm" :class="msg.role === 'user' ? 'bg-indigo-100 text-indigo-900' : 'bg-gray-100 text-gray-900'">
                                <p class="whitespace-pre-wrap" x-text="msg.content"></p>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="loading" class="flex justify-start">
                    <div class="bg-gray-100 text-gray-500 rounded-lg px-4 py-2 text-sm italic">
                        {{ __('Réflexion en cours...') }}
                    </div>
                </div>
            </div>

            <div x-show="error" class="px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded text-sm">
                <p x-text="error"></p>
            </div>

            <form @submit.prevent="sendMessage()" class="flex gap-2">
                <input
                    type="text"
                    x-model="newMessage"
                    placeholder="{{ __('Posez une question sur ce candidat...') }}"
                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    :disabled="loading"
                >
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
                    :disabled="loading || !newMessage.trim()"
                >
                    {{ __('Envoyer') }}
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function chatComponent() {
        return {
            messages: [],
            newMessage: '',
            loading: false,
            error: null,

            init() {
                this.scrollToBottom();
            },

            async sendMessage() {
                const message = this.newMessage.trim();
                if (!message || this.loading) return;

                this.messages.push({ id: Date.now(), role: 'user', content: message });
                this.newMessage = '';
                this.loading = true;
                this.error = null;

                try {
                    const response = await fetch('{{ route("analyses.chat", $analyse) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ message }),
                    });

                    if (!response.ok) throw new Error('Erreur serveur');

                    const data = await response.json();
                    this.messages.push({ id: Date.now() + 1, role: 'assistant', content: data.response });

                    $nextTick(() => this.scrollToBottom());
                } catch (e) {
                    this.error = "{{ __('Une erreur est survenue. Veuillez réessayer.') }}";
                } finally {
                    this.loading = false;
                    this.scrollToBottom();
                }
            },

            scrollToBottom() {
                if (this.$refs.messagesContainer) {
                    this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                }
            }
        };
    }
</script>
@endpush

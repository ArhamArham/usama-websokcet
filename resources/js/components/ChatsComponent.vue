<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 order-10 order-lg-0">
                <div class="card card-default text-white">
                    <div class="card-header bg-dark">Messages</div>
                    <div class="card-body p-0" style="background-image: url('assets/images/bg.jpg');
                        background-size: contain;
                        background-position: center;">

                        <ul class="list-unstyled" style="height: 300px; overflow-y: scroll;" v-chat-scroll>
                            <li class="p-2" v-for="(message, index) in messages" :key="index">
                                <strong>{{ message.user.name }} :</strong>
                                <span class="p-2 bg-dark" style="border-radius: 10px;">{{ message.message }}</span>
                            </li>
                        </ul>

                    </div>
                    <input
                        @keyup="sendTypingEvent"
                        @keyup.enter="sendMessage"
                        v-model="newMessage"
                        type="text"
                        class="form-control bg-dark text-white"
                        name="message"
                        placeholder="Enter your message...">

                </div>
                <span class="text-muted" v-if="activeUser">{{ activeUser.name }} is typing...</span>
            </div>

            <div class="col-12 col-md-4 my-3 mt-lg-0">
                <div class="card card-default">
                    <div class="card-header bg-dark text-white">Active Users</div>
                    <div class="card-body">
                        <ul>
                            <li class="py-2" v-for="user in users">{{ user.name }}</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
    export default {
        props: ['user'],
        data() {
            return {
                messages: [],
                newMessage: '',
                users: [],
                activeUser: false,
                typingTimer: false
            }
        },

        created() {
            this.fetchMessages();
            Echo.join('chat')
                .here(user => {
                    this.users = user;
                })
                .joining(user => {
                    this.users.push(user);
                })
                .leaving(user => {
                    this.users = this.users.filter(function (u) {
                        return u.id !== user.id
                    })
                })
                .listen('MessageSent', (event) => {
                    this.messages.push(event.message)
                })
                .listenForWhisper('typing', user => {
                    this.activeUser = user;

                    if (this.typingTimer) {
                        clearTimeout(this.typingTimer)
                    }

                    this.typingTimer = setTimeout(() => {
                        this.activeUser = false
                    }, 3000)
                })
        },

        methods: {
            fetchMessages() {
                axios.get('messages').then(response => {
                    this.messages = response.data;
                });
            },

            sendMessage() {
                this.messages.push({
                    user: this.user,
                    message: this.newMessage
                });
                axios.post('messages', {message: this.newMessage})
                this.newMessage = '';
            },

            sendTypingEvent() {
                Echo.join('chat')
                    .whisper('typing', this.user);
            }
        }
    }
</script>

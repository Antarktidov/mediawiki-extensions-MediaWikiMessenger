mw.loader.using( [ 'vue', "mediawiki.api" ] ).then( function ( require ) {
    const Vue = require( 'vue' );
    //Vue.config.devtools = true; // Включение режима разработки
    const api = new mw.Api();

    const app = Vue.createApp({
        data() {
            return { 
                message_from_vue_js: "this is vue.js, btw",
                wikiText: "==Заголовок==\n'''Жирный текст'''",
                parsedWikiText: "",
                messages: [],
                channels: [],
                reversedMessages: [],
                currentChannelId: 0,
                scriptPath: '',
                mwMessengerSendMessageBtnTxt: '',
                myMessage: {
                    text: ''
                },
                isUserCanDeleteOtherUsersMessages: false,
            }
        },
        beforeMount() {
            //this.parseWikiText();
            this.getChannels();
            this.scriptPath = mw.config.get('wgScriptPath');
            this.mwMessengerSendMessageBtnTxt = mw.msg('mw-messenger-send-message-btn');
            this.setIsUserCanDeleteOtherUsersMessages();
            console.log('this.isUserCanDeleteOtherUsersMessages', this.isUserCanDeleteOtherUsersMessages);
        },
        methods: {
            async parseWikiText(textBeforeParsing) {
                try {
                    const data = await api.post({
                        action: 'parse',
                        format: 'json',
                        text: textBeforeParsing,
                        contentmodel: 'wikitext'
                    });
                    return data.parse.text['*'];
                } catch (error) {
                    console.error('Ошибка при преобразовании вики-разметки:', error);
                    return '';
                }
            },
            getChannels() {
                api.get({
                    action: 'get_mw_messenger_channels',
                    format: 'json'
                }).done((data) => {
                    this.channels = data['get_mw_messenger_channels'];
                    console.log(this.channels);
                }).fail((error) => {
                    console.error('Error when getting channels:', error);
                });
            },
            async getChannelMessages(channelId) {
                try {
                    const data = await api.get({
                        action: 'get_mw_messenger_channel_messages',
                        format: 'json',
                        channel_id: channelId
                    });
                    this.messages = data['get_mw_messenger_channel_messages'].messages;
                    console.log(this.messages);

                    for (let i = 0; i < this.messages.length; i++) {
                        this.messages[i].parsedMessageText = await this.parseWikiText(this.messages[i].mw_messenger_message_revision_text);
                    }

                    this.reversedMessages = this.reverseArray(this.messages);

                    this.currentChannelId = channelId;
                } catch (error) {
                    console.error('Error when getting messages:', error);
                }
            },
            reverseArray(arr) {
                return arr.reverse();
            },
            sendMyMessage() {
                console.log('msg send btn pressed');

                this.myMessage.text.trim();

                if (this.myMessage.text === '' || this.currentChannelId === 0) {
                    return;
                }

                api.post({
                    action: 'send_message_mw_messenger',
                    format: 'json',
                    message_text: this.myMessage.text,
                    channel_id: this.currentChannelId,
                }).done((data) => {
                    console.log(data);
                    this.myMessage.text = '';
                }).fail((error) => {
                    console.error('Error when sending message:', error);
                });
            },
            setIsUserCanDeleteOtherUsersMessages() {
                this.isUserCanDeleteOtherUsersMessages = mw.config.get('isUserCanDeleteOtherUsersMessages');
            }
        }
    });

    app.mount('#mw-messenger');

    console.log('messenger script is loaded');
});
mw.loader.using( [ 'vue', "mediawiki.api" ] ).then( function ( require ) {
    const Vue = require( 'vue' );
//Vue.config.devtools = true; // Включение режима разработки
    const api = new mw.Api();

    const app = Vue.createApp({
        data() {
            return { 
                messages: [],
                channels: [],
                reversedMessages: [],
                currentChannelId: 0,
                scriptPath: '',
                mwMessengerSendMessageBtnTxt: '',
                mwMessengerEditMessageBtnTxt: '',
                mwMessengerDeleteMessageBtnTxt: '',
                mwMessengerCancelEditMessageBtnTxt: '',
                mwMessengerSaveEditedMessageBtnTxt: '',
                myMessage: {
                    text: ''
                },
                editedMessage: {
                    text: ''
                },
                isUserCanDeleteOtherUsersMessages: false,
                isChannelSet: false,
                userId: 0,
                globalIsMessageEditorOpen: false,
            }
        },
        beforeMount() {
            this.getChannels();
            this.scriptPath = mw.config.get('wgScriptPath');

            this.mwMessengerSendMessageBtnTxt = mw.msg('mw-messenger-send-message-btn');
            this.mwMessengerEditMessageBtnTxt = mw.msg('mw-messenger-edit-message-btn');
            this.mwMessengerCancelEditMessageBtnTxt = mw.msg('mw-messenger-cancel-edit-message-btn');
            this.mwMessengerSaveEditedMessageBtnTxt = mw.msg('mw-messenger-save-edited-message-btn');
            this.mwMessengerDeleteMessageBtnTxt = mw.msg('mw-messenger-delete-message-btn');

            this.isUserCanDeleteOtherUsersMessages = mw.config.get('isUserCanDeleteOtherUsersMessages');
            this.userId = mw.config.get('userId');
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
                        this.messages[i].isMessageEditorOpen = false; // Ensure this property exists
                    }

                    this.reversedMessages = this.reverseArray(this.messages);

                    this.currentChannelId = channelId;
                    this.isChannelSet = true;
                } catch (error) {
                    console.error('Error when getting messages:', error);
                }
            },
            reverseArray(arr) {
                return arr.reverse();
            },
            sendMyMessage() {
                console.log('msg send btn pressed');

                this.myMessage.text = this.myMessage.text.trim();

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
                    this.getChannelMessages(this.currentChannelId);
                }).fail((error) => {
                    console.error('Error when sending message:', error);
                });
            },

            deletedMessage(messageId) {
                api.post({
                    action: 'delete_message_mw_messenger',
                    format: 'json',
                    message_id: messageId,
                }).done((data) => {
                    console.log(data);
                    this.getChannelMessages(this.currentChannelId);
                }).fail((error) => {
                    console.error('Error when deleting message:', error);
                });
            },
            openMessageEditor(messageId) {
                console.log('Кнопка править нажата');

                if (this.globalIsMessageEditorOpen) {
                    return;
                }

                console.log('Проверка пройдена');
        
                this.globalIsMessageEditorOpen = true;

                this.reversedMessages.forEach(element => {
                    if (element.mw_messenger_message_id === messageId) {
                        element.isMessageEditorOpen = true;
                        this.editedMessage.text = element.mw_messenger_message_revision_text;
                    }
                });
            },
            closeMessageEditor(messageId) {
                this.globalIsMessageEditorOpen = false;
                this.reversedMessages.forEach(element => {
                    if (element.mw_messenger_message_id === messageId) {
                        element.isMessageEditorOpen = false;
                        this.editedMessage.text = '';
                    }
                });
            },
            updateMyMessage(messageId) {
                console.log('Кнопка, отвечающая за обновление сообщения, нажата');
                api.post({
                    action: 'edit_message_mw_messenger',
                    format: 'json',
                    message_id: messageId,
                    edited_message: this.editedMessage.text
                }).done((data) => {
                    console.log(data);
                    this.closeMessageEditor(messageId);
                    this.getChannelMessages(this.currentChannelId);
                }).fail((error) => {
                    console.error('Error when updating message:', error);
                });
            }
        }
    });

    app.mount('#mw-messenger');

    console.log('messenger script is loaded');
});
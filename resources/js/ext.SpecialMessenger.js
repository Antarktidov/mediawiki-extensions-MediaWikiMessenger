mw.loader.using( [ 'vue', "mediawiki.api" ] ).then( function ( require ) {
    const Vue = require( 'vue' );
    //Vue.config.devtools = true; // Включение режима разработки
    console.log(Vue.config);
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
                mwMessengerLoadOldMessagesBtnTxt: '',
                mwMessengerLoadNewishMessagesBtnTxt: '',
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
                currentMessagesPage: 0,
                globalLastMessageCreatedAt: '',
                wgChatSocialAvatars: false,
                customReactions: [],
                allEmojis: [],
                sitetitle: '',

                mwMessengerReactionsGroupSmileysAndEmotion: '',
                mwMessengerReactionsGroupPeopleAndBody: '',
                mwMessengerReactionsGroupAnimalsAndNature: '',
                mwMessengerReactionsGroupFoodAndDrink: '',
                mwMessengerReactionsGroupTravelAndPlaces: '',
                mwMessengerReactionsGroupFlags: ''
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
            this.mwMessengerLoadOldMessagesBtnTxt = mw.msg('mw-messenger-load-old-messages-btn');
            this.mwMessengerLoadNewishMessagesBtnTxt = mw.msg('mw-messenger-load-newish-messages-btn');

            this.mwMessengerReactionsGroupSmileysAndEmotion = mw.msg('mw-messenger-reactions-group-smileys-and-emotion');
            this.mwMessengerReactionsGroupPeopleAndBody = mw.msg('mw-messenger-reactions-group-people-and-body');
            this.mwMessengerReactionsGroupAnimalsAndNature = mw.msg('mw-messenger-reactions-group-animals-and-nature');
            this.mwMessengerReactionsGroupFoodAndDrink = mw.msg('mw-messenger-reactions-group-food-and-drink');
            this.mwMessengerReactionsGroupTravelAndPlaces = mw.msg('mw-messenger-reactions-group-travel-and-places');
            this.mwMessengerReactionsGroupFlags = mw.msg('mw-messenger-reactions-group-flags');

            this.sitetitle = mw.msg('sitetitle');

            this.isUserCanDeleteOtherUsersMessages = mw.config.get('isUserCanDeleteOtherUsersMessages');
            this.wgChatSocialAvatars = mw.config.get('wgChatSocialAvatars');
            this.userId = mw.config.get('userId');

            this.getCustomReactions();
            this.allEmojis = this.getStandardEmojis();

            console.log(this.allEmojis);
        },
        /*mounted() {
            setInterval(async () => {
                if (this.currentMessagesPage === 0 &&
                    this.globalIsMessageEditorOpen === false &&
                    this.isChannelSet === true
                ) {
                    //this.getChannelMessages(this.currentChannelId);
                    try {
                        const data = await api.get({
                            action: 'get_mw_messenger_channel_messages',
                            format: 'json',
                            channel_id: this.currentChannelId,
                            page: 0
                        });
                        let newMessages = data['get_mw_messenger_channel_messages'].messages;
                        //console.log(newMessages);

                        this.reverseArray(newMessages);

                        if (JSON.stringify(newMessages) !== JSON.stringify(this.messages)) {
                            for (let i = 0; i <  newMessages.length; i++) {
                                newMessages[i].parsedMessageText = await this.parseWikiText(newMessages[i].mw_messenger_message_revision_text);
                                newMessages[i].isMessageEditorOpen = false; // Ensure this property exists

                                if (this.wgChatSocialAvatars) {
                                    newMessages[i].user_avatar = await this.parseWikiText('{{#avatar:' + newMessages[i].user_name + '}}');
                                }

                                //console.log(newMessages[i].user_name);
                            }
                            this.reversedMessages = newMessages;

                            this.globalLastMessageCreatedAt = this.reversedMessages[this.reversedMessages.length-1].created_at;

                            mw.loader.load(this.scriptPath + '/extensions/PortableInfobox/resources/PortableInfobox.js');
                            mw.loader.load(this.scriptPath + '/extensions/SpoilerSpan/resources/ext.SpoilerSpan.js');
                        }
                    } catch (error) {
                        console.error('Error when getting messages:', error);
                    }
                }
            }, 2000)
        },*/
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
            getCustomReactions() {
                fetch(this.scriptPath + '/index.php/MediaWiki:MessengerReactions?action=raw')
                    .then(response => response.text())
                    .then(text => {
                        console.log(text);

                        const reactionsLines = text.split('\n');
                        console.log(reactionsLines);

                        reactionsLines.forEach(line => {
                            const [reaction_name, reaction_image] = line.split(' ');
                            this.customReactions.push({
                                reaction_name,
                                reaction_image
                            });
                        });
                    })
                    .catch(error => {
                        console.error('Ошибка при получении кастомных реакций:', error);
                    });
            },
            // Функция для получения всех стандартных эмодзи с группировкой по категориям
            getStandardEmojis() {
                // Диапазоны Unicode эмодзи (актуальные на 2023 год)
                const emojiCategories = [
                {
                    name: this.mwMessengerReactionsGroupSmileysAndEmotion,
                    ranges: [
                    { start: 0x1F600, end: 0x1F64F }, // Основные лица
                    { start: 0x1F910, end: 0x1F92F }, // Дополнительные лица
                    { start: 0x1F970, end: 0x1F971 }, // Влюбленные лица
                    { start: 0x1F973, end: 0x1F976 }, // Другие эмоции
                    ],
                },
                {
                    name: this.mwMessengerReactionsGroupPeopleAndBody,
                    ranges: [
                    { start: 0x1F9B0, end: 0x1F9B9 }, // Волосы и тело
                    { start: 0x1F9D0, end: 0x1F9DF }, // Фантастические существа
                    ],
                },
                {
                    name: this.mwMessengerReactionsGroupAnimalsAndNature,
                    ranges: [
                    { start: 0x1F400, end: 0x1F43F }, // Животные
                    { start: 0x1F980, end: 0x1F98F }, // Насекомые
                    { start: 0x1F990, end: 0x1F9BF }, // Другие природа
                    ],
                },
                {
                    name: this.mwMessengerReactionsGroupFoodAndDrink,
                    ranges: [
                    { start: 0x1F32D, end: 0x1F37F }, // Еда и напитки
                    ],
                },
                {
                    name: this.mwMessengerReactionsGroupTravelAndPlaces,
                    ranges: [
                    { start: 0x1F680, end: 0x1F6FF }, // Транспорт
                    { start: 0x1F3A0, end: 0x1F3FF }, // Места и объекты
                    ],
                },
                {
                    name: this.mwMessengerReactionsGroupFlags,
                    ranges: [
                    { start: 0x1F1E6, end: 0x1F1FF }, // Флаги стран
                    { start: 0x1F3F4, end: 0x1F3F4 }, // Особые флаги
                    ],
                },
                // ... другие категории
                ];
            
                const result = [];
            
                emojiCategories.forEach(category => {
                const emojis = [];
                
                category.ranges.forEach(({ start, end }) => {
                    for (let code = start; code <= end; code++) {
                    try {
                        emojis.push({
                        char: String.fromCodePoint(code),
                        code: code.toString(16).toUpperCase().padStart(4, '0'),
                        });
                    } catch (e) {
                        // Пропускаем невалидные символы
                    }
                    }
                });
                
                result.push({
                    name: category.name,
                    emojis: emojis.filter(e => e.char.match(/\p{Emoji}/u)), // Фильтр только эмодзи
                });
                });
            
                return result;
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
            async getChannelMessages(channelId, messagesPage = 0) {
                try {
                    if (this.currentChannelId !== channelId) {
                        this.currentMessagesPage = 0;
                    }

                    const data = await api.get({
                        action: 'get_mw_messenger_channel_messages',
                        format: 'json',
                        channel_id: channelId,
                        page: messagesPage
                    });
                    this.messages = data['get_mw_messenger_channel_messages'].messages;
                    console.log(this.messages);

                    for (let i = 0; i < this.messages.length; i++) {
                        this.messages[i].parsedMessageText = await this.parseWikiText(this.messages[i].mw_messenger_message_revision_text);
                        this.messages[i].isMessageEditorOpen = false; // Ensure this property exists
                        this.messages[i].isReactionsPickerOpen = false;

                        if (this.wgChatSocialAvatars) {
                            this.messages[i].user_avatar = await this.parseWikiText('{{#avatar:' + this.messages[i].user_name + '}}');
                        }

                        console.log(this.messages[i].user_name);
                    }

                    this.reversedMessages = this.reverseArray(this.messages);

                    this.currentChannelId = channelId;
                    this.isChannelSet = true;

                    this.globalLastMessageCreatedAt = this.reversedMessages[this.reversedMessages.length-1].created_at;

                    mw.loader.load(this.scriptPath + '/extensions/PortableInfobox/resources/PortableInfobox.js');
                    mw.loader.load(this.scriptPath + '/extensions/SpoilerSpan/resources/ext.SpoilerSpan.js');
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
            },
            getMoreMessages(channelId) {
                this.getChannelMessages(channelId, ++this.currentMessagesPage);
            },
            getNewestMessages(channelId) {
                if (this.currentMessagesPage <= 0) {
                    return;
                }

                this.getChannelMessages(channelId, --this.currentMessagesPage);
            },
        }
    });

    app.mount('#mw-messenger');

    console.log('messenger script is loaded');
});
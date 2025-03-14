mw.loader.using( [ 'vue', "mediawiki.api" ] ).then( function ( require ) {
    const Vue = require( 'vue' );
    const api = new mw.Api();
    let socket;

    /*function connectWebSocket() {
        socket = new WebSocket('ws://localhost:8080');

        socket.onopen = function() {
            console.log('WebSocket connection established');
        };

        socket.onmessage = function(event) {
            const message = JSON.parse(event.data);
            console.log(message);

            if (message.type === 'newMessage') {
                console.log('Вам поступило новое сообщение по сокетам!');
                let messageText = message.message_text;
                let channelId = message.channel_id;

                console.log('channelId: ', channelId);
                console.log('app.currentChannelId: ', app.currentChannelId);

                if (channelId === app.currentChannelId) {
                    console.log('id-ники каналов совпали');
                    let messageToPushToMessagesArr = {
                        customReactions: {},
                        isMessageEditorOpen: {},
                        isReactionsPickerOpen: {},
                        mw_messenger_message_channel_id: channelId,
                        mw_messenger_message_revision_text: messageText,
                        mw_messenger_message_user_id: message.user_id,
                    };
                    app.messages.push(messageToPushToMessagesArr);

                    console.log(app.messages);
                }
            }
        };

        socket.onclose = function() {
            console.log('WebSocket connection closed, attempting to reconnect');
            setTimeout(connectWebSocket, 1000);
        };

        socket.onerror = function(error) {
            console.error('WebSocket error:', error);
            socket.close();
        };
    }

    connectWebSocket();*/

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
                mwMessengerHistoryMessageBtnTxt: '',
                mwMessengerLoadNewishMessagesBtnTxt: '',
                myMessage: {
                    text: ''
                },
                editedMessage: {
                    text: ''
                },
                isUserCanDeleteOtherUsersMessages: false,
                isUserAllowedToViewMessagesHistory: false,
                isChannelSet: false,
                userId: 0,
                userName: '',
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
            this.mwMessengerHistoryMessageBtnTxt = mw.msg('mw-messenger-history-message-btn')

            this.mwMessengerReactionsGroupSmileysAndEmotion = mw.msg('mw-messenger-reactions-group-smileys-and-emotion');
            this.mwMessengerReactionsGroupPeopleAndBody = mw.msg('mw-messenger-reactions-group-people-and-body');
            this.mwMessengerReactionsGroupAnimalsAndNature = mw.msg('mw-messenger-reactions-group-animals-and-nature');
            this.mwMessengerReactionsGroupFoodAndDrink = mw.msg('mw-messenger-reactions-group-food-and-drink');
            this.mwMessengerReactionsGroupTravelAndPlaces = mw.msg('mw-messenger-reactions-group-travel-and-places');
            this.mwMessengerReactionsGroupFlags = mw.msg('mw-messenger-reactions-group-flags');

            this.sitetitle = mw.msg('sitetitle');

            this.isUserCanDeleteOtherUsersMessages = mw.config.get('isUserCanDeleteOtherUsersMessages');
            this.isUserAllowedToViewMessagesHistory = mw.config.get('isUserAllowedToViewMessagesHistory');
            this.wgChatSocialAvatars = mw.config.get('wgChatSocialAvatars');
            this.userId = mw.config.get('userId');
            this.userName = mw.config.get('userName');

            this.getCustomReactions();
            this.allEmojis = this.getStandardEmojis();

            console.log('customReactions', this.customReactions);

            this.connectWebSocket();
        },
        
        methods: {
            connectWebSocket() {
                socket = new WebSocket('ws://localhost:8080');
                const self = this; // Сохраняем контекст this
        
                socket.onopen = function() {
                    console.log('WebSocket connection established');
                };
        
                socket.onmessage = async function(event) {
                    const message = JSON.parse(event.data);
                    console.log(message);
        
                    if (message.type === 'newMessage') {
                        console.log('Вам поступило новое сообщение по сокетам!');
                        let messageText = message.message_text;
                        let channelId = message.channel_id;
        
                        console.log('channelId: ', channelId);
                        console.log('self.currentChannelId: ', self.currentChannelId); // Используем self вместо this
        
                        if (channelId === self.currentChannelId) {
                            console.log('id-шники каналов совпали');
                            let messageToPushToMessagesArr = {
                                customReactions: {},
                                isMessageEditorOpen: false,
                                isReactionsPickerOpen: false,
                                mw_messenger_message_channel_id: channelId,
                                mw_messenger_message_revision_text: messageText,

                                mw_messenger_message_user_id: message.user_id,
                                user_name: message.user_name,
                            };
                            messageToPushToMessagesArr.parsedMessageText = await self.parseWikiText(messageToPushToMessagesArr.mw_messenger_message_revision_text);
                            
                            if (self.wgChatSocialAvatars) {
                                messageToPushToMessagesArr.user_avatar = await self.parseWikiText('{{#avatar:' + messageToPushToMessagesArr.user_name + '}}');
                            }
                            
                            self.messages.push(messageToPushToMessagesArr);
        
                            console.log(self.messages);
                        }
                    }
                };
        
                socket.onclose = function() {
                    console.log('WebSocket connection closed, attempting to reconnect');
                    setTimeout(self.connectWebSocket, 1000); // Используем self вместо this
                };
        
                socket.onerror = function(error) {
                    console.error('WebSocket error:', error);
                    socket.close();
                };
            },
            sendMessage(msg) {
                if (socket.readyState === WebSocket.OPEN) {
                    socket.send(JSON.stringify(msg));
                } else {
                    console.error('WebSocket is not open. ReadyState: ' + socket.readyState);
                }
            },
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
                                reaction_name: reaction_name,
                                reaction_image: reaction_image,
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
                        /*this.messages[i].standardReactions = {
                            '😍': [1],
                            '❤️': [1],
                        };
                        let customReactionImage = this.customReactions[0].reaction_image;
                        this.messages[i].customReactions = {
                            [customReactionImage]: [1],
                        };*/
                        if  (Array.isArray(this.messages[i].standardReactions)) {
                            this.messages[i].standardReactions = {};
                        }

                        if  (Array.isArray(this.messages[i].customReactions)) {
                            this.messages[i].customReactions = {};
                        }

                        if (this.wgChatSocialAvatars) {
                            this.messages[i].user_avatar = await this.parseWikiText('{{#avatar:' + this.messages[i].user_name + '}}');
                        }

                        //console.log(this.messages[i].user_name);
                    }

                    this.reversedMessages = this.reverseArray(this.messages);

                    this.currentChannelId = channelId;
                    this.isChannelSet = true;

                    this.globalLastMessageCreatedAt = this.reversedMessages[this.reversedMessages.length-1].created_at;

                    mw.loader.load(this.scriptPath + '/extensions/PortableInfobox/resources/PortableInfobox.js');
                    mw.loader.load(this.scriptPath + '/extensions/SpoilerSpan/resources/ext.SpoilerSpan.js');

                    console.log('standard reactions under message 0', this.messages[0].standardReactions);
                    console.log('custom reactions under message 0', this.messages[0].customReactions);
                } catch (error) {
                    console.error('Error when getting messages:', error);
                }
            },
            reverseArray(arr) {
                return arr.reverse();
            },
            async sendMyMessage() {
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

                this.sendMessage({
                    type: 'newMessage',
                    message_text: this.myMessage.text,
                    channel_id: this.currentChannelId,
                    mw_messenger_message_user_id: this.userId,
                    user_name: this.userName,
                });

                let messageToPushToMessagesArr = {
                    customReactions: {},
                    isMessageEditorOpen: false,
                    isReactionsPickerOpen: false,
                    mw_messenger_message_channel_id: this.currentChannelId,
                    mw_messenger_message_revision_text: this.myMessage.text,

                    mw_messenger_message_user_id: this.userId,
                    user_name: this.userName,
                };
                messageToPushToMessagesArr.parsedMessageText = await this.parseWikiText(messageToPushToMessagesArr.mw_messenger_message_revision_text);
                
                if (self.wgChatSocialAvatars) {
                    messageToPushToMessagesArr.user_avatar = await this.parseWikiText('{{#avatar:' + messageToPushToMessagesArr.user_name + '}}');
                }
                
                this.messages.push(messageToPushToMessagesArr);
            },
            addStandardReactionToMsg(msgId, char) {
                console.log(`need add standard reaction ${char} to msg with id ${msgId}`);

                this.reversedMessages.forEach(element => {
                    if (element.mw_messenger_message_id === msgId) {
                        console.log(element.parsedMessageText);

                        // Инициализация объекта стандартных реакций, если он не существует
                        if (!element.standardReactions) {
                            element.standardReactions = {};
                        }

                        // Инициализация массива пользователей для данной реакции, если он не существует
                        if (!element.standardReactions[char]) {
                            element.standardReactions[char] = [];
                        }

                        // Проверка на уникальность идентификатора пользователя в массиве
                        if (!element.standardReactions[char].includes(this.userId)) {
                            element.standardReactions[char].push(this.userId);
                        }

                        api.post({
                            action: 'add_reaction_to_message',
                            format: 'json',
                            message_id: element.mw_messenger_message_id,
                            reaction: char,
                            reaction_type: 'standard',
                        }).done((data) => {
                            console.log(data);
                        }).fail((error) => {
                            console.error('Error when adding custom reaction:', error);
                        });

                        console.log('Message information after adding standard reaction', element);
                    }
                });
            },
            addCustomReactionToMsg(msgId, reaction) {
                console.log(`need add custom reaction ${reaction} to msg with id ${msgId}`);
                this.reversedMessages.forEach(element => {
                    if (element.mw_messenger_message_id === msgId) {
                        console.log(element.parsedMessageText);

                        // Инициализация объекта кастомных реакций, если он не существует
                        if (!element.customReactions) {
                            element.customReactions = {};
                        }

                        // Инициализация массива пользователей для данной реакции, если он не существует
                        if (!element.customReactions[reaction]) {
                            element.customReactions[reaction] = [];
                        }

                        // Проверка на уникальность идентификатора пользователя в массиве
                        if (!element.customReactions[reaction].includes(this.userId)) {
                            element.customReactions[reaction].push(this.userId);
                        }

                        api.post({
                            action: 'add_reaction_to_message',
                            format: 'json',
                            message_id: element.mw_messenger_message_id,
                            reaction: reaction,
                            reaction_type: 'custom',
                        }).done((data) => {
                            console.log(data);
                        }).fail((error) => {
                            console.error('Error when adding custom reaction:', error);
                        });

                        console.log('Message information after adding custom reaction', element);
                    }
                });
            },
            switchStandardReaction(reaction, message) {
                console.log(`need to swith rections. reaction: ${reaction}, message: ${message}`);
                // Проверка, существует ли массив пользователей для данной реакции
                if (!message.standardReactions[reaction]) {
                    message.standardReactions[reaction] = [];
                }

                // Проверка, поставил ли пользователь уже эту реакцию
                const userReactionIndex = message.standardReactions[reaction].indexOf(this.userId);

                if (userReactionIndex === -1) {
                    // Если пользователь еще не поставил эту реакцию, добавляем его ID
                    message.standardReactions[reaction].push(this.userId);
                    api.post({
                        action: 'add_reaction_to_message',
                        format: 'json',
                        message_id: message.mw_messenger_message_id,
                        reaction: reaction,
                        reaction_type: 'standard',
                    }).done((data) => {
                        console.log(data);
                    }).fail((error) => {
                        console.error('Error when adding standard reaction:', error);
                    });
                } else {
                    // Если пользователь уже поставил эту реакцию, удаляем его ID
                    message.standardReactions[reaction].splice(userReactionIndex, 1);

                    api.post({
                        action: 'remove_reaction_from_message',
                        format: 'json',
                        message_id: message.mw_messenger_message_id,
                        reaction: reaction,
                        reaction_type: 'standard',
                    }).done((data) => {
                        console.log(data);
                    }).fail((error) => {
                        console.error('Error when deleting standard reaction:', error);
                    });
                }

                // Если массив пользователей для данной реакции пуст, удаляем реакцию из объекта
                if (message.standardReactions[reaction].length === 0) {
                    delete message.standardReactions[reaction];

                }

                console.log('Message information after switching reaction', message);
            },
            switchCustomReaction(reactionKey, message) {
                console.log(`need to swith rections. reactionKey: ${reactionKey}, message: ${message}`);
                // Проверка, существует ли массив пользователей для данной реакции
                if (!message.customReactions[reactionKey]) {
                    message.customReactions[reactionKey] = [];
                }

                // Проверка, поставил ли пользователь уже эту реакцию
                const userReactionIndex = message.customReactions[reactionKey].indexOf(this.userId);

                if (userReactionIndex === -1) {
                    // Если пользователь еще не поставил эту реакцию, добавляем его ID
                    message.customReactions[reactionKey].push(this.userId);
                    api.post({
                        action: 'add_reaction_to_message',
                        format: 'json',
                        message_id: message.mw_messenger_message_id,
                        reaction: reactionKey,
                        reaction_type: 'custom',
                    }).done((data) => {
                        console.log(data);
                    }).fail((error) => {
                        console.error('Error when adding custom reaction:', error);
                    });
                } else {
                    // Если пользователь уже поставил эту реакцию, удаляем его ID
                    message.customReactions[reactionKey].splice(userReactionIndex, 1);

                    console.log('reactionKey из удаления кастомной реации:', reactionKey);

                    api.post({
                        action: 'remove_reaction_from_message',
                        format: 'json',
                        message_id: message.mw_messenger_message_id,
                        reaction: reactionKey,
                        reaction_type: 'custom',
                    }).done((data) => {
                        console.log(data);
                    }).fail((error) => {
                        console.error('Error when deleting custom reaction:', error);
                    });
                }

                // Если массив пользователей для данной реакции пуст, удаляем реакцию из объекта
                if (message.customReactions[reactionKey].length === 0) {
                    delete message.customReactions[reactionKey];
                }

                console.log('Message information after switching reaction', message);
            },
            deleteMessage(messageId) {
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
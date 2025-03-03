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
                //currentChannelId: 0
            }
        },
        beforeMount() {
            this.parseWikiText();
            this.getChannels();
            //console.log(this.channels);
        },
        methods: {
            parseWikiText() {
                api.post({
                    action: 'parse',
                    format: 'json',
                    text: this.wikiText,
                    contentmodel: 'wikitext'
                }).done((data) => {
                    this.parsedWikiText = data.parse.text['*'];
                }).fail((error) => {
                    console.error('Ошибка при преобразовании вики-разметки:', error);
                });
            },
            getChannels() {
                api.get({
                    action: 'get_mw_messenger_channels',
                    format: 'json'
                }).done((data) => {
                    //this.parsedWikiText = data.parse.text['*'];
                    //console.log(data);
                    this.channels = data['get_mw_messenger_channels'];
                    console.log(this.channels);
                }).fail((error) => {
                    console.error('Error when getting channels:', error);
                });
            },
            getChannelMessages(channelId) {
                api.get({
                    action: 'get_mw_messenger_channel_messages',
                    format: 'json',
                    channel_id: channelId
                }).done((data) => {
                    //console.log(data);
                    this.messages = data['get_mw_messenger_channel_messages'].messages;
                    console.log(this.messages);
                }).fail((error) => {
                    console.error('Error when getting messages:', error);
                });
            }
        }
    });

    app.mount('#mw-messenger');

    console.log('messenger script is loaded');
});
mw.loader.using( [ 'vue', "mediawiki.api" ] ).then( function ( require ) {
    const Vue = require( 'vue' );
    const api = new mw.Api();

    const app = Vue.createApp({
        data() {
            return { 
                message_from_vue_js: "this is vue.js, btw",
                wikiText: "==Заголовок==\n'''Жирный текст'''",
                parsedWikiText: ""
            }
        },
        mounted() {
            this.parseWikiText();
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
            }
        }
    });

    app.mount('#vue-example-special-page');

    console.log('messenger script is loaded');
});
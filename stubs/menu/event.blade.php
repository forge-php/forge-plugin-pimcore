pimcore.registerNS('pimcore.plugin.{{ $name }}');

pimcore.plugin.{{ $name }} = Class.create({

    initialize: function () {
        document.addEventListener(pimcore.events.preMenuBuild, this.preMenuBuild.bind(this));
    },

    preMenuBuild: function (event) {
        let menu = event.detail.menu;

        menu.{{ $name }} = {
            label: t('{{ $name }}.label'),
            handler: this.onMenuOpen.bind(this),
            iconCls: 'pimcore_nav_icon_{{ $name }}',
            priority: 1,
            shadow: false,
            items: [
                /* {
                 *      text: t('{{ $name }}.subItem'),
                 *      handler: function() {},
                 *      iconCls: 'some-icon-class'
                 * }
                 */
            ],
            noSubmenus: true, // change this to true if you have sub menu items
            cls: 'pimcore_navigation_flyout'
        };
    },

    onMenuOpen: function () {
        // build something awesome
    }
});

var {{ $name }}Plugin = new pimcore.plugin.{{ $name }}();



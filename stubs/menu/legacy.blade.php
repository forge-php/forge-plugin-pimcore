pimcore.registerNS("pimcore.plugin.{{ $name }}");

pimcore.plugin.{{ $name }} = Class.create(pimcore.plugin.admin, {

    menuItems: null,

    menuInitialized: false,

    getClassName: function () {
        return "pimcore.plugin.{{ $name }}";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    initializeMenu: function (toolbar, menuItems) {
        if (this.menuInitialized) {
            return;
        }

        // add {{ $name }} main menu
        this.navEl = Ext.get('pimcore_menu_{{ $name }}');
        this.navEl.show();
        this.navEl.on("mousedown", toolbar.showSubMenu.bind(menuItems));

        pimcore.helpers.initMenuTooltips();

        this.menuInitialized = true;
    },

    pimcoreReady: function (params, broker) {
        var toolbar = pimcore.globalmanager.get("layout_toolbar");

        // init
        var menuItems = toolbar.{{ $name }}Menu;
        if (!menuItems) {
            menuItems = new Ext.menu.Menu({
                cls: "pimcore_navigation_flyout",
                listeners: {
                    "show": function (e) {
                        Ext.get('pimcore_menu_{{ $name }}').addCls('active');
                    },
                    "hide": function (e) {
                        Ext.get('pimcore_menu_{{ $name }}').removeCls('active');
                    }
                }
            });
            toolbar.{{ $name }}Menu = menuItems;
        }

        /*
            // Uncomment this block to add child menu
            var item = {
                text: t("pimcore_menu_{{ $name }}_item_1"),
                iconCls: "pimcore_menu{{ $name }}_item_icon_1",
                handler: function () {
                    // do something here
                }
            };

            menuItems.add(item);
        */



        if (menuItems.items.length > 0) {
            this.initializeMenu(toolbar, menuItems);
        }
    },
});

var {{ $name }}Plugin = new pimcore.plugin.{{ $name }}();

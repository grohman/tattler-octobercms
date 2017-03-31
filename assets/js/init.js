$(function () {
    var data = $('script#tattlerJs').data();

    var tattler = tattlerFactory.create({
        urls: {
            ws: '/_tattler/ws',
            channels: '/_tattler/channels',
            auth: '/_tattler/auth'
        },
        autoConnect: false,
        debug: data.debug
    });


    $(document).trigger('tattler.ready', tattler);

    /** @namespace data.rooms */
    for (var room in data.rooms) {
        if (data.rooms.hasOwnProperty(room)) {
            tattler.addChannel(data.rooms[room], false);
        }
    }

    tattler.run();

    window.tattler = tattler;
});
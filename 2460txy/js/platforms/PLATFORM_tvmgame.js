var tvmid = '';
var token = '';
var nickname = '';
var tvmid = '';


var tvmUrl = 'https://open.yx.tvyouxuan.com';
var pf = function(g2b, shareInfo, pf_params, passData) {
    this.g2b = g2b;
    this.shareInfo = shareInfo;
    this.pf_params = pf_params;

    this.passData = passData;
    this.reyunurl = "";
    this.init();
};
pf.prototype = new platform();

pf.prototype.init = function() {
    console.log('init done');
    console.log(JSON.stringify(this.pf_params));
    tvmid = this.pf_params.tvmid;
    token = this.pf_params.token;
    this.g2b.loadScript('//h5sdk.cdn.zytxgame.com/js/jquery.min.js', function() {
        console.log('jquery ok');
    });
    this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);

};

pf.prototype.pay = function(amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + JSON.stringify(orderData));
    var that = this;
    var param = {};
    param.openId = orderData.openId;
    param.openKey = orderData.openKey;
    param.appId = this.passData.appId;
    param.money = amount;
    param.orderNo = orderData.orderNo;
    param.ext = orderData.ext || "";
    param.data = orderData.actor_id; //  put actor_id into data
    param.goodsName = orderData.subject;
    param.cproleid = orderData.cproleid;
    param.platform = this.passData.passId;
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        console.log(JSON.stringify(res));
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var readl_money = amount; // for test
        // var readl_money = 1; // for test
        GetCurrentUser(function(usr) {
            tvmid = usr.tvmid;
            nickname = usr.nickname; // 昵称
            avatar = usr.avatar; // 头像
            token = usr.token;
            var redirectUrl = '//h5sdk.zytxgame.com/html/tvmgame_pay.html';
            var url = "//h5sdk.zytxgame.com/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId + "?";
            url += 'tvmid=' + tvmid;
            url += '&token=' + token;
            url += '&order_id=' + generate_order_id;
            url += '&amount=' + readl_money;
            url += '&description=' + that.shareInfo.title + '-' + readl_money / 10 + param.goodsName;
            url += '&cpcallback=' + decodeURIComponent('http://h5sdk.zytxgame.com/index.php/api/notify/tvmgame/1221');
            url += '&redirect=' + redirectUrl;
            // decodeURIComponent(window.location)
            this.g2b.getDataXHR(url, function(response) {
                if (response.c == 0) {
                    console.log(JSON.stringify(response.d));
                    var that = this;
                    var _url = tvmUrl + '/public/finance/MultiRecharge';
                    // var _url = 'https://open.yx.tvyouxuan.com/public/finance/WebRecharge';


                    var obj = {
                        'tvmid': tvmid,
                        'token': token,
                        'order_id': generate_order_id,
                        'amount': readl_money,
                        'description': response.d.description,
                        'callback': decodeURIComponent('http://h5sdk.zytxgame.com/index.php/api/notify/tvmgame/1221'),
                        'redirect': redirectUrl,
                        'key': response.d.key,
                        'timestamp': response.d.time,
                        'sign': response.d.sign,
                        'nonce': response.d.nonce,
                    }
                    //decodeURIComponent(window.location.href)
                    $.ajax({
                        type: "POST",
                        url: _url,
                        data: JSON.stringify(obj),
                        success: function(res) {
                            console.log('ok');
                            console.log(JSON.stringify(res));
                            // window.location.href = res.data.checkout;
                            OpenNewTab(res.data.checkout);
                        },
                        dataType: "json"
                    });
                    console.log(response.d.sign_str);
                    console.log(_url);


                }


            });
        });
        // OpenNewTab(url);

    });
};
pf.prototype.checkFocus = function(data) {

    // var url = "http://h5sdk.zytxgame.com/index.php/api/focus/" + this.passData.passId + "?openid=" + data.openId;
    // this.g2b.getDataXHR(url, function(response) {
    //     if (response.c == 0) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
    // } else {
    //     this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
    // }
    // });

};
pf.prototype.reportData = function(data) {
    console.log(JSON.stringify(data));
    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var level = data.rolelevel;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
    } else if (data.action == 'create_role') {
        var that = this;

        GetCurrentUser(function(usr) {
            tvmid = usr.tvmid;
            nickname = usr.nickname; // 昵称
            avatar = usr.avatar; // 头像
            token = usr.token;
            var _game_role_id = 'zhtl' + cproleid;
            var _role_name = 'zhtl' + data.rolename;
            var _curl = "//h5sdk.zytxgame.com/index.php/api/focus/" + that.passData.passId + "/" + that.passData.appId + "?tvmid=" + tvmid + "&token=" + token + "&role_name=" + _role_name + "&game_role_id=" + _game_role_id;
            // alert(_curl);
            that.g2b.getDataXHR(_curl, function(response) {

                var _url = tvmUrl + '/public/event/CharacterCreation';

                var obj = {
                    'key': response.d.key,
                    'timestamp': response.d.time,
                    'sign': response.d.sign,
                    'nonce': response.d.nonce,
                    "tvmid": tvmid,
                    "token": token,
                    "game_role_id": response.d.game_role_id,
                    "role_name": response.d.role_name,
                    "role_create_time": response.d.role_create_time,
                };
                console.log(obj);
                // alert(JSON.stringify(obj));
                $.ajax({
                    type: "POST",
                    url: _url,
                    data: JSON.stringify(obj),
                    dataType: "json"
                });


            });
        });



        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {

        });



    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
    console.log("pf showQrCode called");
    // document.getElementById("qr_modal").style.display = "block";
};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};
pf.prototype.showShare = function() {
    console.log('click share button');
    var that = this;

    GetCurrentUser(function(usr) {
        tvmid = usr.tvmid;
        nickname = usr.nickname; // 昵称
        avatar = usr.avatar; // 头像
        token = usr.token;
        var _game_role_id = 'zhtl' + cproleid;
        var _role_name = 'zhtl' + data.rolename;
        var _curl = "//h5sdk.zytxgame.com/index.php/api/focus/" + that.passData.passId + "/" + that.passData.appId + "?tvmid=" + tvmid + "&title=" + that.shareInfo.title + "&content=" + that.shareInfo.desc
        + "&img=" + that.shareInfo.imgUrl+ "&img=" + decodeURIComponent(window.location)+"&isshare=isshare";
        that.g2b.getDataXHR(_curl, function(response) {
            var _url = tvmUrl + '/public/event/Share';
            var obj = {
                "key": response.d.key,
                "tvmid": tvmid,
                "title":  response.d.title,
                "content": response.d.desc,
                "img": response.d.img,
                "url": response.d.url,
                "nonce": response.d.nonce,
                "timestamp": response.d.time,
                "sign": response.d.sign,
            };
            $.ajax({
                type: "POST",
                url: _url,
                data: JSON.stringify(obj),
                dataType: "json"
            });
        });
    });
    this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
}
pf.prototype.isDownloadable = function() {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};

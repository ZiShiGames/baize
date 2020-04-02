var isAndroid;
var isiOS;
var ua;
var from;
var game_id;
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
    console.log('init in');
    if (this.pf_params.appId == 1115) {
        game_id = 6620;
    } else if (this.pf_params.appId == 1218) {
        game_id = 7507;
    } else if (this.pf_params.appId == 1220) {
        game_id = 7508;
    } else {
        game_id = 5645;
    }
    var obj = {
        type: 'dataCount', //
        msg: 'server' //
    }
    window.top.postMessage(obj, 'http://togame.pps.tv');
    window.top.postMessage(obj, 'http://togame.iqiyi.com');
    console.log('init iqiyi' + JSON.stringify(obj));
    var u = navigator.userAgent;
    from = "";
    try {
        from = AppExt.from();
    } catch (e) {};


    console.log(from);
    ua = window.navigator.userAgent.toLowerCase();
    isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端
    isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
    this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
};

pf.prototype.pay = function(amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + orderData);
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
    param.platform = 'iqiyi';
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var real_money = amount / 100;
        // var real_money = 0.01; //  for test
        var post_str = "game_id=" + game_id + "&user_id=" + userId + "&server_id=" + "1" + "&money=" + real_money + "&extra_param=" + generate_order_id;
        console.log(post_str);
        window.parent.postMessage(post_str, "http://togame.pps.tv");
        window.parent.postMessage(post_str, "http://togame.iqiyi.com");
        window.parent.postMessage(post_str, "http://playgame.pps.tv");
        window.parent.postMessage(post_str, "http://playgame.iqiyi.com");
        window.parent.postMessage(post_str, "http://playgame2.iqiyi.com");
        closePayWindow();


    });
};
pf.prototype.checkFocus = function(data) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
};
pf.prototype.reportData = function(data) {
    console.log("reportData " + JSON.stringify(data));
    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var level = data.rolelevel;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        var obj2 = {
            type: 'dataCount', //
            msg: 'start' //用户开始游戏时
        }
        window.top.postMessage(obj2, 'http://togame.pps.tv');
        window.top.postMessage(obj2, 'http://togame.iqiyi.com');
        // var obj1 = {
        //     type: 'dataCount', //
        //     msg: 'server' //用户开始游戏时
        // }
        // window.top.postMessage(obj1, 'http://togame.pps.tv');
        // window.top.postMessage(obj1, 'http://togame.iqiyi.com');
        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var obj = {
            type: 'dataCount', //
            msg: 'role' //用户完成创角时
        }
        window.top.postMessage(obj, 'http://togame.pps.tv');
        window.top.postMessage(obj, 'http://togame.iqiyi.com');
        console.log('init iqiyi' + JSON.stringify(obj));
        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/iqiyi/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
    console.log("pf showQrCode called");
    QqesSdk.follow();
};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.showShare = function() {
    console.log("shareShare"); //因为没有回调，暂时先按照点击后就算分享成功。
    var msg = {
        position: "game_share",
        data: "show"
    };
    window.parent.postMessage(msg, 'http://togame.pps.tv');
    window.parent.postMessage(msg, 'http://togame.iqiyi.com');
    window.parent.postMessage(msg, "http://playgame.pps.tv");
    window.parent.postMessage(msg, "http://playgame.iqiyi.com");
    window.parent.postMessage(msg, "http://playgame2.iqiyi.com");
    this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
}
pf.prototype.isDownloadable = function() {
    // data = {
    // 	"qipuId": "212313220",
    // 	"gameName": "龙城霸业H5",
    // 	"version": "V.3",
    // 	"icon": "http://static.g.iqiyi.com/images/open/201704/58f08b855128c.png",
    // 	"url": "http://cdn.data.video.iqiyi.com/cdn/ppsgame/20170717/upload/unite/pps/H5/lcbyh50717.apk",
    // 	"packName": "com.allugame.aiqiyi",
    // 	"gameType": "角色扮演"
    // };

    if (from == "apk" || isiOS) {
        this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
    } else {
        // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, data);
        this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://cdn.data.video.iqiyi.com/cdn/ppsgame/20170717/upload/unite/pps/H5/lcbyh50717.apk");
    }

};

var p_name;
var iswx;
var channel;
var openid;
var game_id;
var game_name;
var cpUid;
var cpAppid;
var cpChannel
var pf = function(g2b, shareInfo, pf_params, passData) {
    this.g2b = g2b;
    this.shareInfo = shareInfo;
    this.pf_params = pf_params;

    this.passData = passData;
    p_name = passData.passId;
    this.reyunurl = "";
    this.init();
};
pf.prototype = new platform();

pf.prototype.init = function() {
    channel = this.pf_params.channel;
    game_id=this.passData.appId;
    cpUid =this.pf_params.uid;
    cpAppid=this.pf_params.appid;
    switch (game_id) {
        case 1162:
            game_name = '小小村长';
            break;
        case 1237:
            game_name = '就决定是你了';
            break;
        default:

    }
    var that = this;
    var js_two = "https://www.shandw.com/libs/js/sdwJs.min.js";
    var js_one = "https://pay.17m3.com/gamepay/dhpay.min.js";

    this.g2b.loadScript(js_two, function() {
        this.g2b.loadScript(js_one, function() {
            // this.g2b.getDataXHR("http://h5sdk.zytxgame.com/index.php/api/focus/sdwan/" + that.passData.appId, function(response) {
            // if (response.c == 0) {

            var ua = window.navigator.userAgent.toLowerCase();
            if (ua.match(/MicroMessenger/i) == 'micromessenger') {
                openid = that.pf_params.openid;
                iswx = 'true';
            } else {
                openid = '';
                iswx = 'false';
            } //hovertree.com
            that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);
            // }
            // });
        });
    });




};

pf.prototype.pay = function(amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + orderData);
    var param = {};
    var that = this;
    param.openId = orderData.openId;
    param.openKey = orderData.openKey;
    param.appId = this.passData.appId;
    param.money = amount;
    param.orderNo = orderData.orderNo;
    param.ext = orderData.ext || "";
    param.data = orderData.actor_id; //  put actor_id into data
    param.goodsName = orderData.subject;
  param.cproleid = orderData.cproleid;
    param.platform = p_name;
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        console.log(JSON.stringify(res));
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var ext = generate_order_id;
        var readl_money = amount;
        // var readl_money = 1; // for test

        var url = "http://h5sdk.zytxgame.com/index.php/api/sign_order/sdwan/" + that.passData.appId + "?order_id=" + generate_order_id +
            "&money=" + readl_money + "&userId=" + userId + "&ext=" + ext + "&subject=" + param.goodsName + "&memo=" + generate_order_id + "&iswx=" + openid + "&channel=" + channel;
        var js_one = "https://pay.17m3.com/gamepay/dhpay.min.js";
        var js_two = "https://www.shandw.com/libs/js/sdwJs.min.js";
        // this.g2b.loadScript(js_one, function() {
        console.log("js one login");
        // this.g2b.loadScript(js_two, function() {
        console.log("js two login");
        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {
                console.log(response.d.appid);
                if (iswx == 'true') {
                    var _wx = openid;
                } else {
                    var _wx = '';
                }
                var pay_data = {
                    'appId': response.d.appid,
                    'accountId': response.d.uid,
                    'amount': readl_money,
                    'paychannel': "",
                    'wxopenid': _wx,
                    'cpOrderId': "g2460u" + response.d.uid + "m" + readl_money + "t" + response.d.time,
                    'call_back_url': 'http://www.shandw.com/m/game/?gid=' + response.d.appid + '&channel=' + response.d.channel,
                    'merchant_url': 'http://www.shandw.com/m/game/?gid=' + response.d.appid + '&channel=' + response.d.channel,
                    'memo': response.d.memo,
                    'subject': param.goodsName,
                    'channel': channel,
                    'gameName': game_name,
                    'sign': response.d.sign,
                    'timestamp': response.d.time,
                    'complete': "",
                    // 'complete': "function()",
                };
                console.log(JSON.stringify(pay_data));
                sdw.chooseSDWPay(pay_data);

                closePayWindow();
            }
        });


        // });

        // });

        // myOwnBri.startPhone('1');

    });
};
pf.prototype.checkFocus = function(data) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
};
pf.prototype.reportData = function(data) {

    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var level = data.rolelevel;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;
        var obj = {
            'uid':this.pf_params.uid,
            'appid':this.pf_params.appid,
            'channel':this.pf_params.channel,
            'id':this.pf_params.uid,
            'nick':data.rolename,
            'sid':srvid,
            'sname':srvid,
            'level':data.rolelevel,
            'type':'game',
            'vip':'0',
            'power':data.power,
            'new':0,
        };
        sdw.postGameInfo(obj);
        console.log(this.pf_params.channel);
        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        var obj = {
            'uid':this.pf_params.uid,
            'appid':this.pf_params.appid,
            'channel':this.pf_params.channel,
            'id':this.pf_params.uid,
            'nick':data.rolename,
            'sid':srvid,
            'sname':srvid,
            'level':data.rolelevel,
            'type':'game',
            'vip':'0',
            'power':data.rolelevel,
            'new':1,
        };
        sdw.postGameInfo(obj);
        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "/index.php/api/sign_collect/" + passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
pf.prototype.showQrCode = function() {};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};

pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};
pf.prototype.showShare = function() {
    console.log('click share');
    var that = this;
    if(game_id=1162){
        sdw.onSetShareOperate({
            title: that.shareInfo.title,
            desc: that.shareInfo.desc,
            link: "http://www.shandw.com/mi/game/1938401485.html",
            imgUrl: that.shareInfo.imgUrl,
            success: function() {
                alert('share ok');
                that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
            },
            cancel: function() {},
            fail: function() {}
        });
    }



}

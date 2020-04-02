var _nickName = '';
var cp_gameid = '';
var cp_gamename = '';
var cp_openId = '';

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
    cp_gameid = this.pf_params.gameId;
    cp_gamename = this.shareInfo.title;
    cp_openId = this.pf_params.openid;

    this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);

};

pf.prototype.pay = function(amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + JSON.stringify(orderData));
    var that = this;
    var param = {};
    param.openId = orderData.openId; // 2460 用户id
    param.openKey = orderData.openKey; // 2460 验证key
    param.appId = this.passData.appId; // 2460 游戏id
    param.money = amount; // 钱 单位分
    param.orderNo = orderData.orderNo; // 研发游戏订单id
    param.ext = orderData.ext || ""; // serverid || 区服
    param.data = orderData.actor_id; //  put actor_id into data
    param.goodsName = orderData.subject; // 商品名
    param.cproleid = orderData.cproleid;
    param.platform = this.passData.passId;
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        console.log(JSON.stringify(res));
        var generate_order_id = res.d.order_id; // 我们的订单号 || ext
        var userId = res.d.userId;
        var gamerate = amount / 10;
        var readl_money = amount / 100; // for test

        // var readl_money = 0.01;


        var url = "http://" + location.host + "/index.php/api/sign_order/" + param.platform + "/" + param.appId +
            "?userId=" + userId +
            "&gid=" + param.appId +
            "&sid=" + param.ext +
            "&gamename=" + cp_gamename +
            "&readl_money=" + readl_money +
            "&cp_trade_no=" + generate_order_id +
            "&openid=" + cp_openId +
            "&item=" + param.goodsName +
            "&gamerate=" + gamerate +
            "&ybcn=" + param.goodsName +
            "&rolename=" + _nickName;
        // "&openid" + param.openId;

        console.log("url: " + url);


        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {
                var userid = response.d.userid;
                var gid = response.d.gid;
                var sid = response.d.sid;
                var money = response.d.money;
                var gamename = response.d.gamename;
                var cp_trade_no = response.d.cp_trade_no;
                var openid = response.d.openid;
                var method = response.d.method;
                var item = response.d.item;
                var gamerate = response.d.gamerate;
                var rolename = response.d.rolename;
                var sign = response.d.sign;

                var payparm = {
                    'userid': userid,
                    'gid': gid,
                    'sid': sid,
                    'money': money,
                    'gamename': gamename,
                    'cp_trade_no': cp_trade_no,
                    'openid': openid,
                    'method': method,
                    'item': item,
                    'gamerate': gamerate,
                    'ybcn': gamename,
                    'rolename': rolename,
                    'sign': sign
                };

                window.parent.postMessage(payparm, '*');

            }
        });
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
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });

        _nickName = nickName;
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        // sdk.createRole(cp_gameid, srvid, roleid);

        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//" + location.host + "/index.php/api/sign_collect/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
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
    // this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
}
pf.prototype.isDownloadable = function() {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};

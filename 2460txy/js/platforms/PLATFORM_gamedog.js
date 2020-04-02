var p_name = 'gamedog';
var cpChannel = '';
var cpAppid = '';
var cpToken = '';
var gamegd;
var srvid;
var pf = function (g2b, shareInfo, pf_params, passData) {
    this.g2b = g2b;
    this.shareInfo = shareInfo;
    this.pf_params = pf_params;

    this.passData = passData;
    this.reyunurl = "";
    this.init();
};
pf.prototype = new platform();


pf.prototype.init = function () {
    var that = this;
    cpChannel = this.pf_params.channel;
    cpAppid = this.pf_params.appid;
    cpToken = this.pf_params.token;
    this.g2b.loadScript("http://sdk.h5.gamedog.cn/static/js/GDSDK.js", function () {
        gamegd = new GameGD({
            "appid": cpAppid,
            "token": cpToken
        });
        that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);
    });


}

pf.prototype.pay = function (amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + JSON.stringify(orderData));
    console.log(" goodsName " + orderData.subject);
    var param = {};
    param.openId = orderData.openId; // 2460 用户id
    param.openKey = orderData.openKey; // 2460 验证key
    param.appId = this.passData.appId; // 2460 游戏id
    param.money = amount; // 钱 单位分
    param.orderNo = orderData.orderNo; // 厌烦订单id
    param.ext = orderData.ext || ""; // serverid
    param.data = orderData.actor_id; //  put actor_id into data
    param.goodsName = orderData.subject;
    param.cproleid = orderData.cproleid; // 商品名
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function (res) {
        var generate_order_id = res.d.order_id;
        var appid = param.appId;
        var channel = 0;
        var fee = amount / 100;
        // var fee = 0.01; //  for test
        var orderno = generate_order_id;
        var subject = orderData.subject;
        var t = new Date();
        var timestamp = t.getTime();
        var sign;
        var token;
        var ext = generate_order_id;
        var sign_url = 'http://' + location.host + '/index.php/api/sign_order/' + p_name + '/' + param.appId + '?fee=' + fee + '&orderno=' + orderno + '&subject=' + fee + '&timestamp=' +
            timestamp + "&channel=" + cpChannel + "&token=" + cpToken;

        this.g2b.getDataXHR(sign_url, function (response) {
            sign = response.d.sign;
            channel = response.d.channel;
            token = response.d.token;
            appid = response.d.appid;
            timestamp = response.d.time;
            var show_url = "http://sdk.h5.gamedog.cn/pay/index?appid=" + cpAppid + "&channel=" + cpChannel + "&orderno=" +
                orderno + "&subject=" + fee + "&timestamp=" + timestamp + "&sign=" + sign + "&token=" +
                cpToken + "&fee=" + fee + "&ext=" + ext;
            console.log(show_url);
            // window.top.location.href = show_url;
            gamegd.pay({
                appid: cpAppid,
                channel: cpChannel,
                fee: fee, //单位元
                orderno: generate_order_id, //CP方订单号
                subject: fee,
                timestamp: timestamp,
                sign: sign, //建议在服务端生成
                token: cpToken,
                ext: generate_order_id,

            });
        });
        closePayWindow();

    });
};

pf.prototype.checkFocus = function (data) {

};

pf.prototype.reportData = function (data) {

    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var level = data.rolelevel;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        this.g2b.getDataXHR(url, function (response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function (response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//" + location.host + "/index.php/api/sign_collect/" + p_name + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function (response) {});
    }
};
pf.prototype.logout = function () {};
pf.prototype.showQrCode = function () {

};
pf.prototype.isOpenShare = function () {
    // if (this.pf_params.token.indexOf("-") != parseInt(-1)) {
    //     this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
    // } else {
    //     this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
    // }
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);

};
pf.prototype.isDownloadable = function () {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};

pf.prototype.showShare = function () {
    var that = this;
    // var sharedata = {
    //     appid: that.pf_params.appid, //游戏appid
    //     token: that.pf_params.token, //token
    //     serverid: srvid, //区服id
    //     title: that.shareInfo.title, //分享标题
    //     content: that.shareInfo.desc, //分享描述
    //     ext: that.shareInfo.title, //透传参数
    // }

    // gamegd.share(sharedata);

    // gamegd.onShareOK(function () {
        that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
    // });

};

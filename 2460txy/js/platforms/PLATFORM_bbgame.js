var p_name = 'bbgame';
var bb_username;
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
    bb_username = this.pf_params.username;
    this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
}

pf.prototype.pay = function(amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + orderData);
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
    }, function(res) {
        console.log(JSON.stringify(res));

        var generate_order_id = res.d.order_id;
        var u_id = res.d.userId; // 平台用户id
        var roleid = orderData.actor_id;
        var serverid = orderData.ext;
        var money = amount / 100;
        // var amount = 1; //  for test
        var productname = orderData.subject;
        var t = new Date();
        var paytime = t.getTime();
        var attach = generate_order_id;
        var callBackInfo = generate_order_id;
        var sign_url = '//' + location.host + '/index.php/api/sign_order/' + p_name + '/' + param.appId +
            '?username=' + u_id + '&productname=' + productname + '&amount=' + money + '&roleid=' + roleid +
            '&serverid=' + serverid + '&paytime=' + paytime + '&attach=' + attach + '&bb_username=' + bb_username;
        this.g2b.getDataXHR(sign_url, function(response) {
            if (response.c == 0) {
                var appid = response.d.appid;
                var token = response.d.token;
                var username = response.d.username;
                var url = 'http://h5i.niudaosy.com/Pay/index' + '?username=' + bb_username + '&productname=' + productname + '&amount=' + money + '&roleid=' + roleid +
                    '&serverid=' + serverid + '&appid=' + appid + '&paytime=' + paytime + '&attach=' + attach + '&token=' + token;
                console.log(url);
                window.location.href = url;
            }
        });

        closePayWindow();

    });
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
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//" + location.host + "/index.php/api/sign_collect/bbgame/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {

};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};
pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};
pf.prototype.showShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, false);
}

pf.prototype.checkFocus = function(data) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);

};

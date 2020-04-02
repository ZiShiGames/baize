var sdklogindomain;
var sdkloginmodel;
var pf = function(g2b, shareInfo, pf_params, passData) {
    this.g2b = g2b;
    this.shareInfo = shareInfo;
    this.pf_params = pf_params;

    this.passData = passData;
    this.reyunurl = "";
    this.init();
};
pf.prototype = new platform();

var isShowShare = false;

pf.prototype.init = function() {

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
        var generate_order_id = res.d.order_id;

        var money = amount;
        // var money = 1; //  for test
        var uid = res.d.userId; // 平台用户id
        var goodsName = orderData.subject;

        var sign_url = 'http://h5sdk.zytxgame.com/index.php/api/sign_order/novembersixteen/' + param.appId + '?amount=' + money + '&ext=' + generate_order_id;
        console.log(sign_url);
        this.g2b.getDataXHR(sign_url, function(response) {
            var paydata = JSON.stringify({
                'productId': 1,
                'productName': response.d.productName,
                'productDes': response.d.productName,
                'amount': money,
                'ext': generate_order_id,
                'sign': response.d.sign,
            });
            purchase_interface.reqPurchase(paydata);
        });
        closePayWindow();

    });
};

pf.prototype.checkFocus = function(data) {

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

        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/novembersixteen/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {

};

pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.isDownloadable = function() {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};


pf.prototype.showShare = function() {
    var that = this;
    Qdsdk.share(function() {
        that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
    });
}

pf.prototype.showQrCode = function() {
    Qdsdk.follow();
};
pf.prototype.checkFocus = function(data) {


};

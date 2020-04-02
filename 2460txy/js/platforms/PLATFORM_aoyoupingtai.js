var u;
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
    console.log(this.passData);
    this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
};

pf.prototype.pay = function(amount, orderData) {
    var param = {};
    param.openId = orderData.openId;
    param.openKey = orderData.openKey;
    param.appId = this.passData.appId;
    param.money = amount;
    param.orderNo = orderData.orderNo;
    // param.orderNo = orderData.orderNo;
    param.ext = orderData.ext || "";
    param.data = orderData.actor_id; //  put actor_id into data
    param.goodsName = orderData.subject;
  param.cproleid = orderData.cproleid;
    param.platform = 'aoyoupingtai';
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        var generate_order_id = res.d.order_id;
        var user_id = res.d.userId;
        var readl_money = amount / 100;
        // var readl_money = 1 / 100;
        var subject = orderData.subject;
        this.g2b.getDataXHR("http://h5sdk.zytxgame.com/index.php/api/sign_order/" + passId + "/" + param.appId + "?action=" + 'payment' + '&userid=' + user_id + '&appid=1' + '&serverid=' + param.ext + '&orderid=' + generate_order_id + '&money=' + readl_money + '&gamemoney=' + amount + '&attach=' + generate_order_id, function(response) {
            if (response.c == 0) {
                var payData = {
                    action: 'payment',
                    userid: user_id,
                    appid: '1',
                    serverid: param.ext,
                    orderid: generate_order_id,
                    money: readl_money,
                    gamemoney: amount,
                    attach: generate_order_id,
                    notifyurl: response.d.notify,
                    timestamp: response.d.timeStamp,
                    sign: response.d.sign,
                };
                var payUrl = "http://www.gdhuacui.com/cpapi?action=payment" + "&userid=" + user_id + "&appid=1" + "&serverid=" + param.ext + "&orderid=" + generate_order_id + "&money=" + readl_money + "&gamemoney=" + amount + "&attach=" + generate_order_id + "&notifyurl=" + response.d.notify + "&timestamp=" + response.d.timeStamp + "&sign=" + response.d.sign;
                window.location.href = payUrl;

            }
            closePayWindow();
        })

    });
};
pf.prototype.checkFocus = function(data) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
};
pf.prototype.reportData = function(data) {

    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        // var nickName = encodeURIComponent(data.rolename);
        var nickName = data.rolename;
        var level = data.rolelevel;
        var url = "//h5sdk.zytxgame.com/index.php/api/login/aoyoupingtai/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level;

        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        // var nickName = encodeURIComponent(data.rolename);
        var nickName = data.rolename;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/aoyoupingtai/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName;
        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/aoyoupingtai/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
    console.log("pf showQrCode called");

};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};
pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};

// pf.prototype.showShare = function() {
// this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, false);
// }

var u;
var gameId;
var userId;

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
    this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
    gameId = this.pf_params.gameId;
    userId = this.pf_params.userId;
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
    param.platform = 'gametai';
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        var generate_order_id = res.d.order_id;
        var user_id = res.d.userId;
        var readl_money = amount / 100;
        // var readl_money = 1;
        var subject = orderData.subject;
        this.g2b.getDataXHR("//" + location.host + "/index.php/api/sign_order/" + passId + "/" + param.appId + "?amount=" + readl_money + '&server=' + param.ext + '&uid=' + userId + '&order_id=' + generate_order_id + '&gameId=' + gameId, function(response) {
            if (response.c == 0) {
                console.log(response.d);
                window.top.location.href = response.d;
                closePayWindow();
            }
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
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;


        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        // var nickName = encodeURIComponent(data.rolename);
        var nickName = data.rolename;
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {});
        var cpUrl = "http://sdk.yxitai.com/UidSellInfo/CreateRole?appid=1004&roleid=" + userId + "&rolename=" + nickName + "&userid=" + userId + "&serverid=" + srvid + "&servername=" + srvid + "&profession=1";
        console.log(cpUrl);
        this.g2b.getDataXHR(cpUrl, function(response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var url = "//" + location.host + "/index.php/api/sign_collect/gametai/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
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

var sogou_game;
var p_name = 'dazhan';
var game35;
var payDetial;
var u_uid;
var updata_url;
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
    this.g2b.getDataXHR('http://h5sdk.zytxgame.com/index.php/api/focus/' + p_name + '/' + this.passData.appId + '?updata_url=' + updata_url, function(response) {
        u_uid = response.d.uid;
        console.log("uid is " + u_uid);
        g2b.postMessage(g2b.MESSAGES.INIT_CALLBACK);
    });

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
    param.platform = p_name;
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        var orderid = res.d.order_id;
        // var ext = orderid;
        var amount = param.money / 100;
        // var amount = 0.01;
        var productname = orderData.subject;
        var sid = orderData.ext;
        var oid = orderid;
        var role = orderData.actor_id;
        var url = "http://h5.wan855.cn/api/public/js/wan855sdk.js";
        var sign_url = 'http://h5sdk.zytxgame.com/index.php/api/sign_order/' + p_name + '/' + param.appId + "?orderid=" + orderid + "&amount=" + amount + "&productname=" + productname + "&sid=" + sid + "&oid=" + oid + "&role=" + role + '&uid=' + u_uid;
        this.g2b.loadScript(url, function() {
            this.g2b.getDataXHR(sign_url, function(response) {
                payDetial = response.d.pay_infode;
                u_uid = response.d.uid;
                GQC.startPay();
            })
        });

        closePayWindow();
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
        var t = new Date();
        var time = t.getTime();
        updata_url = "http://h5.wan855.cn/api/h5/game/playerInfo?appid\=" + "19" + "\&time\=" + time + "\&rolename\=" + encodeURIComponent(data.rolename) + "\&sid\=" + srvid + "\&unionid\=" + u_uid + "\&level\=" + level;
        console.log(updata_url);
        this.g2b.getDataXHR('http://h5sdk.zytxgame.com/index.php/api/focus/' + p_name + '/' + this.passData.appId + '?updata_url=' + updata_url, function(response) {});
        // this.g2b.getDataXHR(updata_url, function(response) {});
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;


        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + p_name + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName;
        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/" + p_name + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&unionid=" + u_uid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
    console.log("pf showQrCode called");

};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);

};
pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};


pf.prototype.showShare = function() {
    // this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);

};
window.addEventListener('shareok', function() {
    console.log('shareok');
    this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
})

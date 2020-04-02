var u;
var cpAppid;
var cpPf;
var cpAccount;
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
    var that = this;
    this.g2b.loadScript('http://issue.68uxi.com/assets/sdk.min.js', function() {
        cpAppid = that.pf_params.appid;
        cpPf = that.pf_params.pf;
        cpAccount = that.pf_params.account;
        window.H5Play68 = H5Play68.init(cpAppid, cpPf); //参数说明见下方参数表1
        H5Play68.login(cpAccount); //参数说明见下方参数表2
        that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);
    });

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
    param.platform = 'liubaweiyou';
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        var generate_order_id = res.d.order_id;
        var user_id = res.d.userId;
        var readl_money = amount / 100;
        // var readl_money = 1;
        var subject = orderData.subject;
        this.g2b.getDataXHR("http://h5sdk.zytxgame.com/index.php/api/sign_order/" + passId + "/" + param.appId +
            "?money=" + readl_money + '&account=' + cpAccount + '&itemname=' + subject +
            '&attach=' + generate_order_id +
            '&serverid=' + param.ext +
            '&appid=' + cpAppid +
            '&pf=' + cpPf,
            function(response) {
                if (response.c == 0) {
                    H5Play68.pay(cpAccount, subject, readl_money, generate_order_id, response.d.sign, param.ext);

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
        var url = "//h5sdk.zytxgame.com/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;


        this.g2b.getDataXHR(url, function(response) {});
        H5Play68.load(cpAccount, nickName, level, srvid);
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        // var nickName = encodeURIComponent(data.rolename);
        var nickName = data.rolename;
        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
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

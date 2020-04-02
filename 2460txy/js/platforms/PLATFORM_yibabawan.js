var u;
var gkey;
var pkey;
var my_skey;
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
    var login_url = '//' + location.host + '/index.php/api/focus/yibabawan/' + this.passData.appId + '?skey=1';
    console.log(login_url);
    console.log(JSON.stringify(this.pf_params));
    console.log(JSON.stringify(this.passData));
    this.g2b.getDataXHR(login_url, function(response) {

        console.log('init');
        // this.g2b.getDataXHR("http://h5.188wan.com/sdk/load", function() {
        gkey = that.pf_params.gkey;
        pkey = that.pf_params.pkey;
        if (response.c == 0) {
            console.log(' in ');
            // gkey = response.d.gkey;
            // pkey = response.d.pkey;
            // var wanSdk = new wanGame();
            var logindata = {
                gkey: gkey,
                skey: 1,
                openid: response.d.uid,
                time: response.d.time,
                sign: response.d.sign
            };
            console.log(JSON.stringify(logindata));
            // wanSdk.ready(function() {
            wanOpenSdk.login(logindata, function(ret) {
                console.log('登录通知回调结果');
                console.log(ret);
            });
            // });
            that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);

        }
        // });
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
    param.platform = passId;
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        var generate_order_id = res.d.order_id;
        var user_id = res.d.userId;
        var readl_money = amount / 100;
        // var readl_money = 1 / 100;
        var subject = orderData.subject;
        this.g2b.getDataXHR("//" + location.host + "/index.php/api/sign_order/" + passId + "/" + param.appId + "?amount=" + readl_money + '&server=' + param.ext + '&uid=' + user_id + '&subject=' + subject + '&order_id=' + generate_order_id + '&skey=1', function(response) {
            if (response.c == 0) {
                var para = {
                    gkey: gkey,
                    skey: 1,
                    openid: user_id,
                    orderno: generate_order_id,
                    money: readl_money,
                    gold: response.d.gold,
                    remark: response.d.remark,
                    time: response.d.time,
                    sign: response.d.sign,

                };
                // alert(JSON.stringify(para));
                wanOpenSdk.pay(para, function(ret) {
                    if (ret.errno == 0 && ret.errmsg == 'ok') {
                        // 使用以上方式判断前端返回,郑重提示：res.message将在用户支付成功后返回ok，但并不保证它绝对可靠，以服务器订单状态通知为准
                    }
                });
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
        var my_skey = srvid;
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
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var url = "//" + location.host + "/index.php/api/sign_collect/" + passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
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

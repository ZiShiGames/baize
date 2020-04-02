var cpNickname;
var cpLevel;
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
    console.log('init done');
    this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
};

pf.prototype.pay = function (amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + JSON.stringify(orderData));
    var that = this;
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
    param.platform = this.passData.passId;
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function (res) {
        console.log(JSON.stringify(res));
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var readl_money = amount; // for test
        var url = "//" + location.host + "/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId +
            "?order_id=" + generate_order_id +
            "&money=" + readl_money +
            "&openId=" + orderData.openId +
            "&userId=" + userId +
            "&cpNickname=" + cpNickname +
            "&cpLevel=" + cpLevel;
        var payid = '';

        if (that.passData.appId == '1476') {
            readl_money = readl_money + '';
            switch (readl_money) {
                case '30000':
                    payid = "com.hxxy.payid.jj.300";
                    break;
                case '60000':
                    payid = "com.hxxy.payid.jj.600";
                    break;
                case '1000':
                    payid = "com.hxxy.payid.10";
                    break;
                case '2000':
                    payid = "com.hxxy.payid.20";
                    break;
                case '3000':
                    payid = "com.hxxy.payid.30";
                    break;
                case '5000':
                    payid = "com.hxxy.payid.50";
                    break;
                case '10000':
                    payid = "com.hxxy.payid.100";
                    break;
                case '20000':
                    payid = "com.hxxy.payid.200";
                    break;
                case '30000':
                    payid = "com.hxxy.payid.300";
                    break;
                case '40000':
                    payid = "com.hxxy.payid.400";
                    break;
                case '50000':
                    payid = "com.hxxy.payid.500";
                    break;
                case '60000':
                    payid = "com.hxxy.payid.600";
                    break;
                case '80000':
                    payid = "com.hxxy.paid.800";
                    break;
                case '100000':
                    payid = "com.hxxy.payid.1000";
                    break;
                case '120000':
                    payid = "com.hxxy.payid.1200";
                    break;
                case '150000':
                    payid = "com.hxxy.payid.1500";
                    break;
                case '180000':
                    payid = "com.hxxy.payid.1800";
                    break;
                case '200000':
                    payid = "com.hxxy.payid.2000";
                    break;
                case '250000':
                    payid = "com.hxxy.payid.2500";
                    break;
                case '300000':
                    payid = "com.hxxy.payid.3000";
                    break;
                case '1000':
                    payid = "com.hxxy.payid.lb.10";
                    break;
                case '2500':
                    payid = "com.hxxy.payid.25";
                    break;
                case '5000':
                    payid = "com.hxxy.payid.sp.50";
                    break;
                case '4800':
                    payid = "com.hxxy.payid.48";
                    break;
                case '6800':
                    payid = "com.hxxy.payid.68";
                    break;
                case '9800':
                    payid = "com.hxxy.payid.98";
                    break;
                case '18800':
                    payid = "com.hxxy.payid.188";
                    break;
                case '28800':
                    payid = "com.hxxy.payid.288";
                    break;
                case '64800':
                    payid = "com.hxxy.payid.648";
                    break;
                default:
                    console.log(readl_money);
                    console.log(typeof (readl_money));
                    break;
            }
        } else {
            switch (readl_money) {
                case '600':
                    payid = "com.swyx.payid.6";
                    break;
                case '1800':
                    payid = "com.swyx.zk.payid.18";
                    break;
                case '3000':
                    payid = "com.swyx.payid.30";
                    break;
                case '6800':
                    payid = "com.swyx.yk.payid.68";
                    break;
                case '9800':
                    payid = "com.swyx.payid.98";
                    break;
                case '19800':
                    payid = "com.swyx.payid.198";
                    break;
                case '32800':
                    payid = "com.swyx.payid.328";
                    break;
                case '64800':
                    payid = "com.swyx.payid.648";
                    break;
                default:
                    console.log(readl_money);
                    console.log(typeof (readl_money));
                    break;
            }
        }


        var param = {
            id: payid,
            cpprivateinfo: generate_order_id
        }


        console.log(param);

        owsdk.dopay(param, function (order) {
            console.log('ok', order);
        })
    });
};
pf.prototype.checkFocus = function (data) {

    // var url = "//" + location.host + "/index.php/api/focus/" + this.passData.passId + "?openid=" + data.openId;
    // this.g2b.getDataXHR(url, function(response) {
    //     if (response.c == 0) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
    // } else {
    //     this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
    // }
    // });

};
pf.prototype.reportData = function (data) {
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
        cpLevel = level;
        cpNickname = data.rolename;
        this.g2b.getDataXHR(url, function (response) {
            console.log(JSON.stringify(response));
        });
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function (response) {
            console.log(JSON.stringify(response));
        });
    } else if (data.action == 'level_up') {
        var my_roleid = data.roleid;
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        // var nickName = data.rolename;
        var level = data.rolelevel;
        var cproleid = data.cproleid;
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//" + location.host + "/index.php/api/sign_collect/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function (response) {
            console.log(JSON.stringify(response));
        });
    }
};
pf.prototype.logout = function () {};
pf.prototype.showQrCode = function () {
    console.log("pf showQrCode called");
    // document.getElementById("qr_modal").style.display = "block";
};
pf.prototype.isOpenShare = function () {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};
pf.prototype.showShare = function () {
    console.log('click share button');
    this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
}
pf.prototype.isDownloadable = function () {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};

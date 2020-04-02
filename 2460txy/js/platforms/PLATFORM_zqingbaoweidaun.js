var _cproleid;
var cprolename;

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
    var that = this;
    // this.g2b.loadScript("http://h5.zqgame.com/statics/cp/libSDK.js", function() {
    that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);
    // });

};

pf.prototype.pay = function(amount, orderData) {
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
    }, function(res) {
        console.log(JSON.stringify(res));
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var readl_money = amount; // for test


        var url = "//" + location.host + "/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId +
            "?order_id=" + generate_order_id +
            "&money=" + readl_money +
            // "&openId=" + userId +
            "&srid=" + param.ext +
            "&cproleid=" + _cproleid +
            "&cprolename=" + cprolename +
            "&userId=" + userId +
            "&goodsName=" + (param.goodsName);
        // var that = this;
        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {
                // SDK.pay(response.d.data);
                var d = response.d;
                window.myOwnBri.pay(d.game_order, d.goods_name, parseInt(d.amount), 1, d.goods_desc, d.create_time, d.ext, d.accid, d.account, '123', d.session, d.sign, d.plat_order, d.notice_url);


            }
        });

    });
};
pf.prototype.checkFocus = function(data) {

    // var url = "//" + location.host + "/index.php/api/focus/" + this.passData.passId + "?openid=" + data.openId;
    // this.g2b.getDataXHR(url, function(response) {
    //     if (response.c == 0) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
    // } else {
    //     this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
    // }
    // });

};
// {"identify":"g2460","msg":"user_level_up","data":{"server":"1","roleId":"1202000004","roleName":"敬嘉澍","level":"17"}}
window.addEventListener("message", function(e) {
    var msg = e.data;
    if (msg.identify && msg.identify == "g2460") {
        switch (msg.msg) {
            case 'user_level_up':
                console.log('level up' + msg.data.roleId + ' ' + msg.data.level);
                var roleid = msg.data.roleId;
                var lv = msg.data.level;
                console.log('level up1 ' + roleid + ' ' + lv);
                window.myOwnBri.levelUp(msg.data.server, parseInt(roleid), msg.data.roleName, parseInt(lv));
                break;
        }
    }
})
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
        cprolename = nickName;
        _cproleid = cproleid;
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
        window.myOwnBri.enter(level, srvid, srvid, cproleid, data.rolename);
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
        window.myOwnBri.createRole(srvid,parseInt(cproleid), data.rolename, parseInt(1));
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
    this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
}
pf.prototype.isDownloadable = function() {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};

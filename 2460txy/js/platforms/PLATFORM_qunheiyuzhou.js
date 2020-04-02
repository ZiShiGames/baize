var alluGameId;
var cpSrvid;
var cpNickName;
var cpRoleId;
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
    alluGameId = this.passData.appId;
    var username = this.pf_params.username;
    var qhchannel = this.pf_params.qhchannel;
    var qhchannelid = this.pf_params.qhchannelid;
    var qhchannelid = this.pf_params.qhchannelid;
    var time = this.pf_params.time;
    this.g2b.loadScript("//port.2r3r.com/game/qhjssdk", function() {

        this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
        console.log(this.pf.pf_params);

        var initdata = {
            "username": username, //用户id，群黑登录接口里面username参数
            "gid": '4102', //群黑游戏id，可以在后台游戏列表查询
            "qhchannel": qhchannel, //用户标识，群黑登录接口里面qhchannel参数
            "qhchannelid": qhchannelid, //用户标识id，群黑登录接口里面qhchannelid参数
            "time": time//用户登录时间戳，群黑登录接口里面time参数
        };
        qhsdk.init(initdata);
        console.log(initdata);
    });

};

pf.prototype.pay = function(amount, orderData) {
    if (alluGameId == 1031) {
        return;
    };
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
    param.platform = this.passData.passId;
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        // alert("res " + res.d);
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var readl_money = amount / 100;
        // var readl_money = 1;
        // alert("jump_to " + jump_to);


        var url = "//" + location.host + "/index.php/api/sign_order/"+ param.platform + "/" + param.appId + "?money=" + readl_money +
            "&userId=" + encodeURIComponent(userId) +
            "&ext=" + generate_order_id;

        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {

                // var jump_to = 'http://m.qunhei.com/pay/gopay/gid/3317.html?' +
                //     "&money=" + readl_money +
                //     "&goodsName=" + encodeURIComponent(param.goodsName) +
                //     "&userId=" + encodeURIComponent(userId) +
                //     "&ext=" + generate_order_id +
                //     "&roleName=" + encodeURIComponent(orderData.appUserName) +
                //     "&sign=" + response.d;
                //
                // window.top.location.href = jump_to;

                var paydata = {
                    "userId": userId,
                    "gid": response.d.gid,
                    "roleName": userId,
                    'goodsId': readl_money,
                    "goodsName": orderData.subject,
                    "money": readl_money,
                    "ext": generate_order_id,
                    "serverId": cpSrvid,
			        "roleId":cpRoleId,
                    "sign": response.d.sign
                };
                // console.log(code + ',' + msg);
                qhsdk.pay(paydata, function(code, msg) {
                    //充值结果通知，code为编号，msg为信息。该结果不能作为发货依据。
                    //code=1充值成功 ，其他为充值失败。
                    console.log("code:", code, msg);
                });

            }
        });
        // closePayWindow();
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
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;
        cpSrvid = srvid;
        cpNickName = data.rolename;
        cpRoleId = cproleid;

        this.g2b.getDataXHR(url, function(response) {});
        var roledata = {
            "act": "2",
            "serverid": srvid,
            "rolename": data.rolename,
            "roleid": cproleid,
            "level": level
        };
        qhsdk.role(roledata);
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {});

        var roledata = {
            "act": "1",
            "serverid": srvid,
            "rolename": data.rolename,
            "roleid": cproleid,
            "level": "1"
        };
        qhsdk.role(roledata);
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//" + location.host + "/index.php/api/sign_collect/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
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
    var that = this;
    qhsdk.share();
    window.addEventListener('shareok', function(e) {
        that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
    });
}

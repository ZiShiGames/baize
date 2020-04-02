var cpNickname;
var cpLevel;
var cpSrvid;
var cpUserId;
var cpUserName;
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
    cpUserId = this.pf_params.userId
    cpUserName = this.pf_params.userName
    this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
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
        var readl_money = amount / 100; // for test
        var cpPlatformName = 'plkj';

        var url = "//" + location.host + "/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId +
            "?orderId=" + generate_order_id +
            "&money=" + readl_money +
            "&source=" + cpPlatformName +
            "&goodsName=" + param.goodsName +
            "&ext=" + generate_order_id +
            "&userId=" + cpUserId +
            "&userName=" + cpUserName;

        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {
                var data = {
                    source: cpPlatformName,
                    pId: response.d.pId,
                    userId: response.d.userId,
                    userName: response.d.userName,
                    gameId: response.d.gameId,
                    goodsId: readl_money,
                    goodsName: param.goodsName,
                    money: readl_money,
                    orderId: generate_order_id,
                    areaServer: cpSrvid,
                    roleName: cpNickname,
                    ext: generate_order_id,
                    gameUrl: "http://" + location.host + "/index.php/enter/play/" + that.passData.passId + "/" + that.passData.appId,
                    time: response.d.time,
                    sign: response.d.sign
                }

                var jump_to = 'http://h5.yuu1.com/Api/Leagues/Pay/trade'+ that.g2b.object2search(data);
                window.top.location.href = jump_to;
                console.log(' get in ajax and notify is ' + jump_to);
                /**
                * 0：充值成功
                1：游戏编号错误
                2：代理商标识错误
                3：账号不存在
                4：签名错误
                5：订单号重复或者订单号不合法
                6：充值金额错误
                */
            }
        });

    });
};
pf.prototype.checkFocus = function(data) {
    console.log(JSON.stringify(data));
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
};
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
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;
        cpSrvid = srvid;
        cpLevel = level;
        cpNickname = data.rolename;
        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });

        // 创角上报
        var pre = 'UserInfo';
        var str = '{"userId":"'+cproleid+'","sId":"'+srvid+'","roleName":"'+cpNickname+'"}';
        window.parent.postMessage( pre + str, '*');

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
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.showShare = function() {
    var that = this;
    window.parent.postMessage('yuu1_share', '*');

}
pf.prototype.isDownloadable = function() {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};

window.addEventListener('message',function(event) {
    if(event.origin !== 'http://h5.yuu1.com') return;
    if(event.data == 'shareok') {
        console.log('分享完成')
        this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
    }
    if(event.data == 'followok') {
        console.log('关注完成')
    }
}, false);

var cpNickname;
var cpLevel;
var cpRoleid;
var cpSrvid;
var cpUid;
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
    cpUid = this.pf_params.user_id;
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
        var readl_money = amount / 100; // for test
        var url = "//" + location.host + "/index.php/api/sign_order/" + param.platform + "/" + param.appId;
        this.g2b.getDataXHR(url, function (response) {
            if (response.c == 0) {
                var orderInfo = {
                    productCode: response.d.productCode,
                    uid: cpUid,
                    username: '',
                    userRoleId: cpRoleid,
                    userRoleName: cpNickname,
                    serverId: cpSrvid,
                    userServer: cpSrvid,
                    userLevel: cpLevel,
                    cpOrderNo: generate_order_id,
                    amount: readl_money,
                    count: 1,
                    quantifier: '个',
                    subject: param.goodsName,
                    desc: param.goodsName,
                    callbackUrl: response.d.callbackUrl,
                    extrasParams: generate_order_id,
                    goodsId: readl_money
                };
                var orderInfoJson = JSON.stringify(orderInfo);
                QuickSDK.pay(orderInfoJson, function (payStatusObject) {
                    console.log('GameDemo:下单通知' + JSON.stringify(payStatusObject));
                    console.log(payStatusObject.status);
                });
            } else {
                alert("网络错误！请重新刷新页面试试~");
            }
        });

        /*
        var url = "//" + location.host + "/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId;
        this.g2b.getDataXHR(url, function (response) {
            if (response.c == 0) {
            }
        });
        */

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
        cpRoleid = cproleid;
        cpSrvid = srvid;
        this.g2b.getDataXHR(url, function (response) {
            console.log(JSON.stringify(response));
        });
        // 渠道登录上报
        var roleInfo = {
            isCreateRole: false,
            roleCreateTime: Math.round(new Date().getTime() / 1000).toString(),
            uid: cpUid,
            serverId: srvid,
            serverName: srvid,
            userRoleName: cpNickname,
            userRoleId: cproleid,
            userRoleBalance: currency,
            vipLevel: 1,
            userRoleLevel: level,
            partyId: 1,
            partyName: 1,
            gameRoleGender: '',
            gameRolePower: power,
            partyRoleId: 1,
            partyRoleName: '',
            professionId: 1,
            profession: '',
            friendlist: ''
        };
        var roleInfoJson = JSON.stringify(roleInfo);
        QuickSDK.uploadGameRoleInfo(roleInfoJson, function (response) {
            console.log(response)
            if (response.status) {
                document.getElementById('uploadMessage').innerHTML = '提交信息成功';
            } else {
                document.getElementById('uploadMessage').innerHTML = response.message;
            }
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
        // 渠道登录上报
        var roleInfo = {
            isCreateRole: true,
            roleCreateTime: Math.round(new Date().getTime() / 1000).toString(),
            uid: cpUid,
            serverId: srvid,
            serverName: srvid,
            userRoleName: nickName,
            userRoleId: cproleid,
            userRoleBalance: 0,
            vipLevel: 1,
            userRoleLevel: 1,
            partyId: 1,
            partyName: 1,
            gameRoleGender: '',
            gameRolePower: 0,
            partyRoleId: 1,
            partyRoleName: '',
            professionId: 1,
            profession: '',
            friendlist: ''
        };
        var roleInfoJson = JSON.stringify(roleInfo);
        QuickSDK.uploadGameRoleInfo(roleInfoJson, function (response) {
            console.log(response)
            if (response.status) {
                document.getElementById('uploadMessage').innerHTML = '提交信息成功';
            } else {
                document.getElementById('uploadMessage').innerHTML = response.message;
            }
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
pf.prototype.logout = function () {
    QuickSDK.setLogoutNotification(function (logoutObject) {
        console.log('Game:玩家点击注销帐号');
        console.log(logoutObject);
    })
};
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

// QuickSDK.setSwitchAccountNotification(function (callbackData) {
//     QuickSDK.logout(function (logoutObject) {
//         console.log('Game:成功退出游戏');
//         alert(JSON.stringify(logoutObject));
//     })
//     alert(JSON.stringify(callbackData));
//     // location.reload();
// });

var cplv;
var cpname;
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
    var that = this;
    CommonMrSdk.getMrPlatform();
    that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);


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


        var url = "//" + location.host + "/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId + "?order_id=" + generate_order_id +
            "&money=" + readl_money + "&openId=" + orderData.openId + "&userId=" + userId + "&goodsName=" + encodeURIComponent(param.goodsName);

        // this.g2b.getDataXHR(url, function(response) {
        //     if (response.c == 0) {
        //
        //     }
        // });

        var payid;
        if (param.goodsName == '月卡') {
            payid = "1000";
        } else {
            switch (readl_money) {
                case 1000:
                    payid = "11";
                    break;
                case 2000:
                    payid = "22";
                    break;
                case 3000:
                    payid = "3";
                    break;
                case 5000:
                    payid = "4";
                    break;
                case 10000:
                    payid = "100";
                    break;
                case 20000:
                    payid = "200";
                    break;
                case 30000:
                    payid = "300";
                    break;
                case 40000:
                    payid = "88";
                    break;
                case 50000:
                    payid = "9";
                    break;
                case 60000:
                    payid = "10";
                    break;
                case 80000:
                    payid = "800";
                    break;
                case 100000:
                    payid = "12";
                    break;
                case 120000:
                    payid = "13";
                    break;
                case 150000:
                    payid = "14";
                    break;
                case 180000:
                    payid = "15";
                    break;
                case 200000:
                    payid = "17";
                    break;
                case 250000:
                    payid = "177";
                    break;
                case 300000:
                    payid = "18";
                    break;
                    // case 600:
                    //     payid = "1";
                    //     break;
                    // case 1800:
                    //     payid = "2";
                    //     break;
                    // case 3000:
                    //     payid = "3";
                    //     break;
                    // case 5000:
                    //     payid = "4";
                    //     break;
                    // case 9800:
                    //     payid = "5";
                    //     break;
                    // case 19800:
                    //     payid = "6";
                    //     break;
                    // case 29800:
                    //     payid = "7";
                    //     break;
                    // case 38800:
                    //     payid = "8";
                    //     break;
                    // case 3000:
                    // 	payid = "1000";
                    // 	break;
                default:

            }
        }
        // var payid;
        // switch (readl_money) {
        //     case 1000:
        //         payid = 519;
        //         break;
        //     case 2000:
        //         payid = 520;
        //         break;
        //     case 3000:
        //         payid = 521;
        //         break;
        //     case 5000:
        //         payid = 522;
        //         break;
        //     case 10000:
        //         payid = 523;
        //         break;
        //     case 20000:
        //         payid = 524;
        //         break;
        //     case 30000:
        //         payid = 525;
        //         break;
        //     case 40000:
        //         payid = 526;
        //         break;
        //     case 50000:
        //         payid = 527;
        //         break;
        //     case 60000:
        //         payid = 528;
        //         break;
        //     case 80000:
        //         payid = 529;
        //         break;
        //     case 100000:
        //         payid = 530;
        //         break;
        //     case 120000:
        //         payid = 531;
        //         break;
        //     case 150000:
        //         payid = 532;
        //         break;
        //     case 180000:
        //         payid = 533;
        //         break;
        //     case 200000:
        //         payid = 534;
        //         break;
        //     case 250000:
        //         payid = 535;
        //         break;
        //     case 300000:
        //         payid = 536;
        //         break;
        //     case 2500:
        //         payid = 537;
        //         break;
        //     case 1800:
        //         payid = 538;
        //         break;
        //     case 1000:
        //         payid = 539;
        //         break;
        //     case 28800:
        //         payid = 540;
        //         break;
        //     case 18800:
        //         payid = 541;
        //         break;
        //     case 9800:
        //         payid = 542;
        //         break;
        //     case 6800:
        //         payid = 543;
        //         break;
        //     case 4800:
        //         payid = 544;
        //         break;
        //     case 64800:
        //         payid = 545;
        //         break;
        //     case 5000:
        //         payid = 546;
        //         break;
        //     default:
        //
        // }


        var payEntity = {}
        payEntity.roleid = param.cproleid; //用户角色 ID
        payEntity.productid = payid; //产品 id， mr 提供
        payEntity.rolename = cpname; //用户角色名
        payEntity.rolelevel = cplv; //用户角色等级
        payEntity.extradata = generate_order_id //服务器透传数据
        payEntity.serverid = Math.abs(param.ext) //服务器 id
        payEntity.gamecno = generate_order_id //游戏内的订单号
        payEntity.channel = '1' //支付渠道，传 1 即可
        payEntity.notifyurl = 'http://' + location.host + '/index.php/api/notify/' + that.passData.passId + "/" + that.passData.appId //研发服务器回传的地址,详见服务 器文档
        // alert(JSON.stringify(payEntity));
        console.log(payEntity);
        CommonMrSdk.pay(payEntity, new function () {
            this.onSuccess = function (responseData) {
                console.log(responseData);
            }
            this.onFail = function (mrError) {
                console.log(mrError);
            }
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
    // alert(JSON.stringify(data));
    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        var srvid = Math.abs(data.srvid);
        var nickName = encodeURIComponent(data.rolename);
        var level = data.rolelevel;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;
        cplv = level;
        cpname = data.rolename;
        // this.g2b.getDataXHR(url, function(response) {
        //     console.log(JSON.stringify(response));
        // });
        var roleEntity = {}
        roleEntity.roleid = cproleid; //角色 id
        roleEntity.serverId = srvid;
        roleEntity.roleName = data.rolename;
        roleEntity.roleLevel = level;
        roleEntity.vipLevel = '1' //角色 vip 等级
        console.log(roleEntity);
        console.log(CommonMrSdk.sendRoleLoginData(roleEntity));
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = Math.abs(data.srvid);
        var nickName = encodeURIComponent(data.rolename);
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        // this.g2b.getDataXHR(url, function(response) {
        //     console.log(JSON.stringify(response));
        // });
        var roleEntity = {}
        roleEntity.roleid = cproleid; //角色 id
        roleEntity.serverId = srvid; //服务器 id
        roleEntity.roleName = data.rolename //角色名称
        roleEntity.roleLevel = '1' //角色等级
        roleEntity.vipLevel = '1' //角色 vip 等级

        CommonMrSdk.sendRoleCreateData(roleEntity)
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = Math.abs(data.srvid);

        var url = "//" + location.host + "/index.php/api/sign_collect/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        // this.g2b.getDataXHR(url, function(response) {
        //     console.log(JSON.stringify(response));
        // });
    } else if (data.action == 'logout') {
        CommonMrSdk.logOut(
            new function () {
                this.onSuccess = function (res) {
                    console.log(res);
                    window.location.reload();
                }
                this.onFail = function (e) {
                    console.log(e);
                }
            }
        );
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
CommonMrSdk.registerLogout(new function () {
    this.onSuccess = function (data) {
        window.location.reload();
    }
    this.onFail = function (mrError) {}
})
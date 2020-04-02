var alluGameId;
var g_g2b;
var useraccount;
var sdkappid;
var pf;
var shareNumber;
var s_nickName = 0;
var s_level = 0;
var s_srv;
var s_cproleid;
var XCVGAMEH5SDK;
var s_roleid = 0;
var s_isOpenShare = false;
var pf = function (g2b, shareInfo, pf_params, passData) {
    this.g2b = g2b;
    g_g2b = g2b;
    this.shareInfo = shareInfo;
    this.pf_params = pf_params;

    this.passData = passData;
    console.log(JSON.stringify(passData));
    this.reyunurl = "";
    this.init();
};
pf.prototype = new platform();

pf.prototype.init = function () {
    alluGameId = this.passData.appId;
    var hlmy_gw = "";
    var appKey = "";
    var that = this;
    console.log('init in');
    sdkappid = this.pf_params.sdkappid;
    pf = this.pf_params.pf;
    useraccount = this.pf_params.useraccount;
    this.g2b.getDataXHR('http://' + location.host + '/index.php/api/focus/xcyx/' + this.passData.appId, function (response) {
        this.g2b.loadScript("http://www.xcvgame.cn/js/xcvgame_sdk.js?v=" + new Date().getTime(), function () {
            console.log(s_nickName);
            console.log(s_level);
            if (response.c == 0) {
                window.XCVGAMEH5SDK = XCVGAMEH5SDK.init(sdkappid, pf);
                console.log('share info ');
                that.g2b.loadScript("//cdn.bootcss.com/jquery/3.1.1/jquery.min.js", function () {
                    $.ajax({
                        url: "//www.xcvgame.cn/api/showshare?sdkappid=" + sdkappid + "&pf=" + pf,
                        type: "GET",
                        dataType: "jsonp", //指定服务器返回的数据类型
                        success: function (data) {
                            console.log("share data");
                            console.log(data.showshare);
                            if (data.showshare == 1) {
                                s_isOpenShare = true;
                            }
                        }
                    });
                });
                if (response.showshare == 1) {

                }

                // console.log(XCVGAMEH5SDK.showshare(sdkappid, pf));
                // if (XCVGAMEH5SDK.showshare(sdkappid, pf) == 1) {
                //
                //     s_isOpenShare = true;
                // }

            }

            this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
        });
    });
}

pf.prototype.pay = function (amount, orderData) {
    if (alluGameId == 1032) {
        return;
    }
    console.log("amount " + amount);
    console.log("orderData " + orderData);
    console.log(" goodsName " + orderData.subject);
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
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function (res) {
        var generate_order_id = res.d.order_id;

        var real_money = amount / 100;
        var real_money_temp = "" + real_money;
        // var real_money = 1; //  for test

        var goodsName = orderData.subject;
        var roleName = orderData.appUserName;
        if (alluGameId == 1391) {
            switch (amount) {
                case 1000:
                    real_money_temp = "" + 1;
                    break;
                case 2000:
                    real_money_temp = "" + 2;
                    break;
                case 3000:
                    real_money_temp = "" + 3;
                    break;
                case 5000:
                    real_money_temp = "" + 4;
                    break;
                case 10000:
                    real_money_temp = "" + 5;
                    break;
                case 20000:
                    real_money_temp = "" + 6;
                    break;
                case 30000:
                    real_money_temp = "" + 7;
                    break;
                case 40000:
                    real_money_temp = "" + 8;
                    break;
                case 50000:
                    real_money_temp = "" + 9;
                    break;
                case 60000:
                    real_money_temp = "" + 10;
                    break;
                case 80000:
                    real_money_temp = "" + 11;
                    break;
                case 100000:
                    real_money_temp = "" + 12;
                    break;
                case 120000:
                    real_money_temp = "" + 13;
                    break;
                case 150000:
                    real_money_temp = "" + 14;
                    break;
                case 180000:
                    real_money_temp = "" + 15;
                    break;
                case 200000:
                    real_money_temp = "" + 16;
                    break;
                case 250000:
                    real_money_temp = "" + 17;
                    break;
                case 300000:
                    real_money_temp = "" + 18;
                    break;
                case 2500:
                    real_money_temp = "" + 1000;
                    break;
                case 1800:
                    real_money_temp = "" + 1001;
                    break;
                case 1000:
                    real_money_temp = "" + 1200;
                    break;
                case 4800:
                    real_money_temp = "" + 1300;
                    break;
                case 6800:
                    real_money_temp = "" + 1301;
                    break;
                case 9800:
                    real_money_temp = "" + 1302;
                    break;
                case 18800:
                    real_money_temp = "" + 1303;
                    break;
                case 28800:
                    real_money_temp = "" + 1304;
                    break;
                case 64800:
                    real_money_temp = "" + 1305;
                    break;
                case 5000:
                    real_money_temp = "" + 1400;
                    break;
                default:

            }
        } else if (alluGameId == 1421) {
            switch (amount) {

                case 600:
                    real_money_temp = "" + 6;
                    break;
                case 3000:
                    realmoney_temp = "" + 30;
                    break;
                case 10000:
                    real_money_temp = "" + 100;
                    break;
                case 50000:
                    real_money_temp = "" + 500;
                    break;
                case 200000:
                    real_money_temp = "" + 2000;
                    break;
                default:

            }

        }

        var url = 'http://' + location.host + '/index.php/api/sign_order/xcyx/' + param.appId + '?money=' + real_money + '&itemname=' + real_money_temp + '&attach=' + generate_order_id + '&serviceid=1&sdkappid=' + sdkappid + '&pf=' + pf + '&useraccount=' + useraccount + "&nickname=" + s_nickName + "&srv=" + param.ext;
        console.log(url);
        this.g2b.getDataXHR(url, function (response) {
            if (response.c == 0) {
                // XCVGAMEH5SDK.init(sdkappid, pf);
                // window.XCVGAMEH5SDK = XCVGAMEH5SDK.init(sdkappid, pf);

                XCVGAMEH5SDK.pay(useraccount, real_money_temp, real_money, generate_order_id, param.ext, response.d.nickname, response.d.sign);
            } else {
                alert('支付失败 请截图反馈客服 ');
            }

        });

        closePayWindow();

    });
};

pf.prototype.checkFocus = function (data) {

};

pf.prototype.reportData = function (data) {
    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        s_roleid = roleid;
        var nickName = encodeURIComponent(data.rolename);
        s_nickName = nickName;
        var level = data.rolelevel;
        s_level = level;
        s_srv = srvid;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        s_cproleid = cproleid;
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;
        // XCVGAMEH5SDK = XCVGAMEH5SDK.init(sdkappid, pf);
        // XCVGAMEH5SDK.createrole(useraccount,srvid,srvid,cproleid,data.rolename,s_level,1);
        // XCVGAMEH5SDK = XCVGAMEH5SDK.init(sdkappid, pf);
        // XCVGAMEH5SDK.updaterole(useraccount,srvid,srvid,cproleid,data.rolename,s_level,1);
        var jsonstr = '{"power":"' + power + '","moneynum":"' + currency + '","balance":"' + currency + '","partyname":" ","partyrolename":" ","partyid":" ","professionid":" ","profession":" ","gender":"0"}';
        console.log(jsonstr);
        XCVGAMEH5SDK.logingame(useraccount, s_srv, s_srv, s_cproleid, s_nickName, s_level, 1, jsonstr);

        // window.XCVGAMEH5SDK = XCVGAMEH5SDK.init(sdkappid, pf);
        XCVGAMEH5SDK.updaterole(useraccount, s_srv, s_srv, s_cproleid, s_nickName, s_level, 1);
        this.g2b.getDataXHR(url, function (response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function (response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//" + location.host + "/index.php/api/sign_collect/xcyx/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function (response) {});
    }
};
pf.prototype.logout = function () {};
pf.prototype.showQrCode = function () {
    console.log("pf showQrCode called");

};
pf.prototype.isOpenShare = function () {

    if (s_isOpenShare) {
        this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
    }
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);

};


pf.prototype.showShare = function () {

    var that = this;
    // XCVGAMEH5SDK.sharecallback(useraccount, function() {
    //     var that = this;
    //     that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
    //     that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
    // });
    XCVGAMEH5SDK.share(useraccount);
    // that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);


    // console.log(window.shareok);
    // if (window.shareok) {
    //
    // 	window.shareok = false;
    // }
};

// function sharecallback() {
// 	console.log('shareok');
//
//
// };
pf.prototype.isDownloadable = function () {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};

function shareok() {
    var that = this;
    that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
}

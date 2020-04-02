var g_g2b;
var is_inited = false;
var alluGameId;
var hlmysdk;
var gid = '';
var that = this;

function getCookie(name) //获取cookie中的值
{
    var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
    if (arr = document.cookie.match(reg))
        return unescape(arr[2]);
    else
        return null;
}
var pf = function (g2b, shareInfo, pf_params, passData) {
    this.g2b = g2b;
    g_g2b = g2b;
    this.shareInfo = shareInfo;
    this.pf_params = pf_params;

    this.passData = passData;
    this.reyunurl = "";
    this.init();
};
pf.prototype = new platform();

pf.prototype.init = function () {
    var hlmy_gw = "";
    var appKey = "";
    var that = this;
    this.g2b.loadScript('//res.1758.com/sdk/js/1758sdk.js', function () {
        var authData = {
            appKey: that.pf_params.appKey, // 链接上携带的参数
            hlmy_gw: that.pf_params.hlmy_gw, //渠道参数
            userToken: that.pf_params.userToken, // 用户令牌
            callback: function (data) {
                console.log(data);
                that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);
            }
        }
        hlmysdk = window.HLMY_SDK;
        that.g2b.getDataXHR("//" + location.host + "/index.php/api/focus/" + that.passData.passId + "/" + that.passData.appId + "?userToken=" + that.pf_params.userToken + "&appKey=" + that.pf_params.appKey + "&hlmy_gp=" +
            that.pf_params.hlmy_gp + "&hlmy_gw=" + that.pf_params.hlmy_gw + "&nonce=" + that.pf_params.nonce + "&sign=" + that.pf_params.sign + "&timestamp=" + that.pf_params.timestamp,
            function (response) {
                console.log(JSON.stringify(response.d));
                gid = response.d;
                hlmysdk.init({
                    "gid": response.d, //通过"用户验证"接口获取到的1758平台gid
                    "appKey": that.pf_params.appKey, //游戏的appkey
                    "hlmy_gw": that.pf_params.hlmy_gw //1758平台的自定义参数，CP通过授权回调地址后的参数获得
                });
                hlmysdk.auth(authData)
                hlmysdk.adaptParams(function (obj) {
                    //obj为一个json对象
                    console.log(obj)
                    var data = obj.data.adaptParams;
                    for (var i in data) {
                        console.log(data[i]);
                        if (data[i].key == 'share.enable' && data[i].value == "true") {
                            console.log('open share');
                            that.g2b.postMessage(that.g2b.MESSAGES.RETURNSHARE, true);
                        } else if (data[i].key == 'share.enable' && !data[i].value == "false") {
                            that.g2b.postMessage(that.g2b.MESSAGES.RETURNSHARE, false);
                        }
                        if (data[i].key == 'follow.enable' && data[i].value == "true") {
                            console.log('open follow');
                            that.g2b.postMessage(that.g2b.MESSAGES.FOCUS_RETURNSTATE, 1);
                            hlmysdk.checkFollow(function (obj) {
                                //obj为一个对象，obj.follow为用户关注状态
                                //0为未关注，1未已关注
                                console.log("obj" + obj.follow);
                                that.g2b.postMessage(that.g2b.MESSAGES.FOCUS_RETURNSTATE, obj.follow);
                                // this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
                            });
                        } else if (data[i].key == 'follow.enable' && !data[i].value == "false") {
                            that.g2b.postMessage(that.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
                        }
                    }
                });
            });


    });



}

pf.prototype.pay = function (amount, orderData) {
    if (alluGameId == 1040) {
        return;
    }
    console.log("amount " + amount);
    console.log("orderData " + orderData);
    console.log(" goodsName " + orderData.subject);
    var param = {};
    var that = this;
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
        // var real_money = 1; //  for test

        var goodsName = orderData.subject;
        var roleName = orderData.appUserName;
        var callBackInfo = generate_order_id;

        // this.g2b.loadScript("//wx.1758.com/static/common/js/1758sdk.js", function() {


        var url = '//' + location.host + '/index.php/api/sign_order/one/' + param.appId + '?money=' + real_money +
            '&txId=' + generate_order_id + '&gid=' + gid + '&appKey=' + that.pf_params.appKey + '&hlmy_gw=' + that.pf_params.hlmy_gw + '&openId=' + orderData.openId + '&goodsName=' + encodeURIComponent(param.goodsName);

        this.g2b.getDataXHR(url, function (response) {
            if (response.c == 0) {
                //console.log(" paySafecode : " + response.d);
                // var hlmysdk = window.HLMY_SDK;
                // hlmysdk.init({
                //     "gid": response.d.gid, //通过"用户验证"接口获取到的1758平台gid
                //     "appKey": response.d.appKey, //游戏的appkey
                //     "hlmy_gw": response.d.hlmy_gw //1758平台的自定义参数，CP通过授权回调地址后的参数获得
                // });
                hlmysdk.pay({
                    "paySafecode": response.d.sign
                });
            } else {}
        });
        //通过sign_order来获取session中存储的appKey等值，暂时没想到其它方法，为了保密我们可以使用post请求，但初始化还是将数据暴露在外。



        // });


        closePayWindow();

    });
};

pf.prototype.checkFocus = function (data) {
    //该方法需要传递一个回调函数
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
};

pf.prototype.reportData = function (data) {

    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var level = data.rolelevel;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

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

        var url = "//" + location.host + "/index.php/api/sign_collect/one/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function (response) {});
    }
};
pf.prototype.logout = function () {};
pf.prototype.showQrCode = function () {
    console.log("pf showQrCode called");
    hlmysdk.follow(); //该方法会弹出二维码，以便让用户关注
};
pf.prototype.isOpenShare = function () {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
}


pf.prototype.showShare = function () {
    console.log("shareShare");
    hlmysdk.setShareInfo({
        "state": "",
        "tipInfo": true,
        "reward": []
    });

}
var _postMessage = function (msg, d) {
    var data = {};
    data.identify = "g2460";
    data.msg = msg;
    data.data = d;
    gameFrame.contentWindow.postMessage(data, "*")
};

function onShareTimeline() {
    this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
    // that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
    // var data = {};
    // data.identify = "g2460";
    // data.msg = 'msg_share_cb';
    // data.data = 'true';
    // // alert('2');
    // window.postMessage(data, "*");
    // // alert('1');
    // _postMessage('msg_share_cb',true);
    // that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
    //  msg_share_cb
    // g_g2b.postMessage(g_g2b.MESSAGES.SHARE_CALLBACK, true);
    // alert('2');
    //
    // alert('3');
}

pf.prototype.isDownloadable = function () {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};

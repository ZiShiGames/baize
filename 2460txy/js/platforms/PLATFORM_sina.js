var appkey = '';
var cpPageid='';
var pf = function(g2b, shareInfo, pf_params, passData) {
    this.g2b = g2b;
    this.shareInfo = shareInfo;
    this.pf_params = pf_params;

    this.passData = passData;
    this.reyunurl = "";
    this.init();
};
pf.prototype = new platform();

var isShowShare = false;

pf.prototype.init = function() {
    var that = this;
    this.g2b.loadScript("http://mg.games.sina.com.cn/kjava/sng/js/share_game.js", function() {

    });

    var url = "http://h5sdk.zytxgame.com/index.php/api/init/sina/" + this.passData.appId;
    this.g2b.getDataXHR(url, function(response) {
        if (response.c == 0) {
            if (response.d.usertype == 99) {
                isShowShare = true;
            }
            appkey = response.d.appkey;

            if(that.pf_params.appId==1223){
                cpPageid='231307c2c2e21532ecac3889e8a0e10823c59f';
            }


            that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);
        }
    });


}

pf.prototype.pay = function(amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + orderData);
    console.log(" goodsName " + orderData.subject);
    var param = {};
    param.openId = orderData.openId; // 2460 用户id
    param.openKey = orderData.openKey; // 2460 验证key
    param.appId = this.passData.appId; // 2460 游戏id
    param.money = amount; // 钱 单位分
    param.orderNo = orderData.orderNo; // 厌烦订单id
    param.ext = orderData.ext || ""; // serverid
    param.data = orderData.actor_id; //  put actor_id into data
    param.goodsName = orderData.subject;
    param.cproleid = orderData.cproleid; // 商品名
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        var generate_order_id = res.d.order_id;

        var real_money = amount;
        // var real_money = 1; //  for test
        var uid = res.d.userId; // 平台用户id
        var subject = orderData.subject;
        var desc = orderData.subject;
        var t = new Date();
        var timestamp = t.getTime();
        var sign_url = 'http://h5sdk.zytxgame.com/index.php/api/sign_order/sina/' + param.appId;
        var access_token;
        var appkey;

        this.g2b.getDataXHR(sign_url, function(response) {
            access_token = response.d.token;
            appkey = response.d.appkey;
            sina_show_url = window.location.href;
            var show_url = "http://m.game.weibo.cn/payment/order/order_cashier?appkey=" + appkey + "&access_token=" + access_token + "&amount=" +
                real_money + "&uid=" + uid + "&subject=" + encodeURIComponent(subject) + "&desc=" + encodeURIComponent(desc) + "&show_url=" +
                sina_show_url + "&pt=" + generate_order_id + "&timestamp=" + timestamp;
            // console.log(show_url);
            window.top.location.href = show_url;
        });
        closePayWindow();

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
        var url = "//h5sdk.zytxgame.com/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/sina/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {

};
pf.prototype.isOpenShare = function() {
    if (share_game.can_share()) {
        this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
    } else {
        this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
    }
};
pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};

pf.prototype.showShare = function(message) {
    console.log(share_game.can_share());
    var that = this;
    if(cpPageid){
        share_game.share_weibo({
            page_id: cpPageid, //上线时，运营人员会给新的pageid，替换一下即可
            content: that.shareInfo.desc, //填写分享的内容，可为空
            app_key: appkey, //填写app_key,pc端分享时候用到
            token: that.pf_params.access_token, //填写token
            uid: that.pf_params.uid, //填写uid
            success: function() {
                //成功回调-必写
                that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
            },
            error: function() {
                //失败回调-不是必写
                // that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
            }
        });
    }




    // if (message != null && message != "") {
    //     var share_url = "http://h5sdk.zytxgame.com/index.php/api/focus/sina/" + this.passData.appId + "?words=" + message;
    //     console.log(share_url);
    //     this.g2b.getDataXHR(share_url, function(response) {
    //         console.log("1");
    //         if (response.c == 0) {
    //             that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
    //         }
    //     });
    // }

};

pf.prototype.weiboShare = function(message) {
    var that = this;
    if (message != null && message != "") {
        var share_url = "http://h5sdk.zytxgame.com/index.php/api/focus/sina/" + this.passData.appId + "?words=" + message;
        console.log(share_url);
        this.g2b.getDataXHR(share_url, function(response) {
            console.log("1");
            if (response.c == 0) {
                that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
            }
        });
    }

};

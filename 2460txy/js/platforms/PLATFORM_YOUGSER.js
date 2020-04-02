/**
 * Created by Yutou on 2016年9月20日15:42:59
 */
var pf = function(h5gamecn, shareInfo, pf_params, passData) {
    this.h5gamecn = h5gamecn;
    this.shareInfo = shareInfo;
    this.pf_params = pf_params;

    this.passHost = 'http://passa.gz.1251010508.clb.myqcloud.com/pass_a';
    this.passData = passData;
    this.reyunurl = "";
    this.init();
};
pf.prototype = new platform();

pf.prototype.init = function() {
    var that = this;
    that.h5gamecn.loadScript("http://cdn.11h5.com/static/js/sdk.min.js", function() {
        var sdk = window.AWY_SDK;
        sdk.config(that.passData.gameId, function(type) {
            that.h5gamecn.postMessage(that.h5gamecn.MESSAGES.SHARE_CALLBACK, true);
        }, function() {
            // 支付成功回调方法（仅针对于快捷支付方式有效）
        });
        sdk.shareDesc(that.shareInfo.desc);
        that.h5gamecn.postMessage(that.h5gamecn.MESSAGES.INIT_CALLBACK);
    });
};

pf.prototype.pay = function(amount, orderData) {
    var param = {};
    param.openId = orderData.openId;
    param.openKey = orderData.openKey;
    param.appId = this.passData.appId;
    param.money = amount;
    param.appOrderId = orderData.orderNo;
    param.ext = orderData.ext || "";
    param.data = '';
    param.goodsName = orderData.subject || ((amount == 2800) ? "月卡" : amount / 10 + '元宝');
    var search = this.h5gamecn.object2search(param);
    this.h5gamecn.createPay({
        search: search
    }, function(res) {
        var payurl = res.payUrl;
        var data = {};
        var p = payurl.split('?')[1].split('&');
        for (var i = 0; i < p.length; i++) {
            data[p[i].split('=')[0]] = p[i].split('=')[1];
        }
        if (!data.txid) {
            toastMsg('订单错误，请重试');
            document.getElementById('loading').style.display = 'none';
            return;
        }
        document.getElementById('loading').style.display = 'none';
        var sdk = window.AWY_SDK;
        sdk.pay(data);
    });
};
pf.prototype.checkFocus = function(data) {
    
};
pf.prototype.reportData = function(data) {
    var action;
    var reyunAppId = 'haiweiys18888s72';
    var reyunUrl = 'http://www.gank-studio.com';
    var that = this;
    if (data.action == 'login') {
        action = '/receive/login';
        this.h5gamecn.getDataXHR(reyunUrl + action, function() {

        }, {
            type: 'post',
            data: JSON.stringify({
                appid: reyunAppId,
                who: data.openId,
                deviceid: data.openId,
                serverid: data.server,
                channelid: that.passData.appId,
                idfa: '',
                idfv: '',
                level: data.level || 1
            })
        }, 'application/x-www-form-urlencoded;charset=UTF-8');
        //心跳
        setInterval(function() {
            console.log('reyunhb');
            var action = '/receive/online';
            this.h5gamecn.getDataXHR(reyunUrl + action, function() {

            }, {
                type: 'post',
                data: JSON.stringify({
                    appid: reyunAppId,
                    who: data.openId,
                    deviceid: data.openId,
                    serverid: data.server,
                    channelid: appid,
                    level: -1
                })
            }, 'application/x-www-form-urlencoded;charset=UTF-8');
        }, 300000);
    }
    if (data.action == 'create_role') {
        action = '/receive/register';
        this.h5gamecn.getDataXHR(reyunUrl + action, function() {

        }, {
            type: 'post',
            data: JSON.stringify({
                appid: reyunAppId,
                who: data.openId,
                deviceid: data.openId,
                serverid: data.server,
                channelid: that.passData.appId,
                idfa: '',
                idfv: '',
                accounttype: '',
                gender: '',
                age: '',
            })
        }, 'application/x-www-form-urlencoded;charset=UTF-8');
    }
};
pf.prototype.logout = function() {
    var sdk = window.AWY_SDK;
    sdk.logout();
};
pf.prototype.showQrCode = function() {
    window.AWY_SDK.showFocus();
};
pf.prototype.isOpenShare = function() {
    this.h5gamecn.postMessage(this.h5gamecn.MESSAGES.RETURNSHARE, true);
};
pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};

var u;
var pf = function(g2b, shareInfo, pf_params, passData) {
	this.g2b = g2b;
	this.shareInfo = shareInfo;
	this.pf_params = pf_params;

	this.passData = passData;
	p_name = passData.passId;
	this.reyunurl = "";
	this.init();
};
pf.prototype = new platform();

pf.prototype.init = function() {
	console.log(this.passData);
	var that = this;
	this.g2b.getDataXHR("http://h5sdk.zytxgame.com/index.php/api/focus/" + passId + "/" + this.passData.appId, function(response) {
		if (response.c == 0) {
			this.g2b.loadScript('http://sdk.jdangame.com/h5distribute/js/newjssdk.js', function() {
				var that = this;
				SDK.init(response.d.mark, response.d.gamename, '', function() {
					that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
				}, '', '', response.d.userid, '', '', '');
				this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
			});
		}
	});
};

pf.prototype.pay = function(amount, orderData) {
	var param = {};
	param.openId = orderData.openId;
	param.openKey = orderData.openKey;
	param.appId = this.passData.appId;
	param.money = amount;
	param.orderNo = orderData.orderNo;
	// param.orderNo = orderData.orderNo;
	param.ext = orderData.ext || "";
	param.data = orderData.actor_id; //  put actor_id into data
	param.goodsName = orderData.subject;
  param.cproleid = orderData.cproleid;
	param.platform = 'meishengyuan';
	var search = this.g2b.object2search(param);
	this.g2b.createPay({
		search: search
	}, function(res) {
		var generate_order_id = res.d.order_id;
		var user_id = res.d.userId;
		var readl_money = amount / 100;
		// var readl_money = 1;
		var subject = param.goodsName;
		this.g2b.getDataXHR("http://h5sdk.zytxgame.com/index.php/api/sign_order/" + passId + "/" + param.appId + "?amount=" + readl_money + '&server=' + param.ext + '&uid=' + user_id + '&order_id=' + generate_order_id + '&goodsName=' + subject, function(response) {
			if (response.c == 0) {
				SDK.pay(generate_order_id, readl_money, param.ext, user_id, param.goodsName);
				closePayWindow();
			}
		})

	});
};
pf.prototype.checkFocus = function(data) {
	this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
};
pf.prototype.reportData = function(data) {

	if (data.action == 'enterGame') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		// var nickName = encodeURIComponent(data.rolename);
		var nickName = data.rolename;
		var level = data.rolelevel;
		var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;
		SDK.loginDataCommit(roleid, nickName, level, srvid, srvid, ' ');
		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'create_role') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		// var nickName = encodeURIComponent(data.rolename);
		var nickName = data.rolename;
		var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'enterCreate') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/meishengyuan/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
		this.g2b.getDataXHR(url, function(response) {});
	}
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
	console.log("pf showQrCode called");

};
pf.prototype.isOpenShare = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};
pf.prototype.isDownloadable = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};

pf.prototype.showShare = function() {
	var that = this;
	SDK.share();
};

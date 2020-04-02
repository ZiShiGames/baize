var u;
var jxwSv;
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
	// this.g2b.getDataXHR("http://h5sdk.zytxgame.com/index.php/api/focus/" + passId + "/" + this.passData.appId, function(response) {
	// if (response.c == 0) {
	this.g2b.loadScript('http://www.jxw123.com/js/h5sdk.v1.js', function() {
		jxwSv = new JxwSv({
			uid: that.pf_params.uid, //用户id
			token: that.pf_params.token //用户 token，登录口令
		});
		// console.log(JSON.stringify(jxwSv));
		this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
	});
	// }
	// });
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
	param.platform = 'jiuxiangwan';
	var search = this.g2b.object2search(param);
	this.g2b.createPay({
		search: search
	}, function(res) {
		var generate_order_id = res.d.order_id;
		var user_id = res.d.userId;
		var readl_money = amount;
		// var readl_money = 1;
		var subject = param.goodsName;
		this.g2b.getDataXHR("//"+location.host+"/index.php/api/sign_order/" + passId + "/" + param.appId + "?amount=" + readl_money + '&server=' + param.ext + '&uid=' + user_id + '&order_id=' + generate_order_id + '&goodsName=' + subject, function(response) {
			if (response.c == 0) {
				var pay_data = {
					orderid: generate_order_id,
					money: readl_money,
					product: subject,
					appid: response.d.appid,
					sign: response.d.sign
				};
				jxwSv.pay(pay_data);
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
        var url = "//"+location.host+"/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'create_role') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		// var nickName = encodeURIComponent(data.rolename);
		var nickName = data.rolename;
		var cproleid = data.cproleid;
        var url = "//"+location.host+"/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'enterCreate') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		var url = "//"+location.host+"/index.php/api/sign_collect/jiuxiangwan/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
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
	jxwSv.share({
		title: that.pf_params.title,
		content: that.pf_params.desc,
	});
	//设置分享完成回调，当用户分享完成时执行
	jxwSv.onShareOK(function() {
		alert('分享成功');
		that.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
	});

}

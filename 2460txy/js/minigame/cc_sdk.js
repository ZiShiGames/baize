var cc = function () {
    var initStatus = false;
    var ios_or_android = ''; // 判断用户设备终端
    var url = 'https://api.baizegame.com/index.php';

    // 初始化
    var init = function (cb) {
        if (!initStatus) {
            initStatus = true
            console.log('yes');
            let appid = this.getParameter('appid');
            let open__id = this.getParameter('open_id');
            let full_url = this.object2search(this.getParameters());
            console.log(full_url);

//          打开微信小程序vconsole
//            if (appid == "wxdbc194faa7400f99") {
//                this.loadScript("https://api.baizegame.com/js/minigame/vconsole.min.js?v=1", function () {
//                    var vConsole = new VConsole();
//                })
//            }

            this.getDataXHR(url + '/wx_minigame/miniprogram_init?appid=' + appid + '&url=' + encodeURIComponent(window.top.location.href), function (res) {
                wx.config({
                    // debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                    appId: res.d.appid, // 必填，公众号的唯一标识【果壳公众号APPID】
                    timestamp: res.d.timestamp, // 必填，生成签名的时间戳
                    nonceStr: res.d.nonceStr, // 必填，生成签名的随机串
                    signature: res.d.signature, // 必填，签名
                    jsApiList: [
                        "previewImage",
                        "scanQRCode"
                    ] // 必填，需要使用的JS接口列表
                });
                wx.ready(function () {
                    console.log('inwx ready ');
                    wx.checkJsApi({
                        jsApiList: ['scanQRCode', 'previewImage'],
                        success: function (res) {
                            console.log('check succ');
                            console.log(res);
                        }
                    });
                });
                wx.error(function (res) {
                    // config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名。
                    console.log('config error');
                    console.log(res);
                });

            })

            // 获取用户终端设备信息
            this.getUA()

            cb && cb()
        } else {
            console.log('no');
        }
    }

    // 获取用户终端设备信息
    var getUA = function () {
        var u = navigator.userAgent;
        isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端
        isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
        if (isiOS) {
            ios_or_android = 'ios';
        } else if (isAndroid) {
            ios_or_android = 'android';
        } else {
            ios_or_android = 'other';
        }
        console.log('用户手机类型:', ios_or_android)
    }

    // 分享
    var share = function (share_info) {
        console.log(share_info);
        share_info.user_id = this.getParameter('user_id');
        var params = this.object2search(share_info)
        wx.miniProgram.navigateTo({
            url: '/pages/share/share' + params
        })
    }

    // 联系客服
    var service = function () {
        wx.miniProgram.navigateTo({
            url: '/pages/service/service'
        })
    }

    // 玩家假投诉
    var advice = function () {
        wx.miniProgram.navigateTo({
            url: '/pages/adviceEnter/adviceEnter'
        })
    }

    // 跳转小程序支付
    var jumpToPay = function (order_info) {
        console.log('in pay ', ios_or_android);
        switch (ios_or_android) {
            case 'android':
                // 切页面直接调起支付
                this.pay(order_info);
                break;
            case 'ios':
            default:
                // 弹小程序码，长按识别二维码，跳小程序支付
                this.jump_pay(order_info);
                break;
        }
    }
    var loadScript = function (url, callback) {
        var script = document.createElement("script");
        script.type = "text/javascript";
        script.onload = function () {
            callback()
        };
        script.onerror = function () {
            script.parentNode.removeChild(script);
            setTimeout(function () {
                loadScript(url, callback)
            }, 1000)
        };
        script.src = url;
        document.getElementsByTagName("head")[0].appendChild(script)

    }
    // 显示小程序码，长按识别二维码图片，跳转小程序支付
    var jump_pay = function (order_info) {
    	console.log('cp_url:'+JSON.stringify(order_info));
        let open_id = this.getParameter('open_id');
        let user_id = this.getParameter('user_id');
        let appid = this.getParameter('appid');
        let channel = this.getParameter('channel');
        let ser_id = order_info.server_id;
        // console.log('open_id:' + open_id, 'user_id:' + user_id, 'appid:' + appid, 'channel:' + channel, 'ser_id:' + ser_id)
        var _url = url + '/wx_minigame/jump_to_pay?open_id=' + open_id + '&appid=' + appid +
            '&product=' + order_info.product +
            '&product_id=' + order_info.product_id +
            '&money=' + order_info.money +
            '&order_id=' + order_info.order_id +
            '&cp_role_id=' + order_info.cp_role_id +
            '&user_id=' + user_id +
            '&channel=' + channel +
            '&server_id=' + ser_id +
        	'&current_role_level=' + order_info.current_role_level;
        this.getDataXHR(_url, function (res) {
            console.log('kf:'+res)
            if(res.d.is_pay=='1'){
            	wx.miniProgram.navigateTo({
            		url: '../shell/jump_kf?error=1&text=' + res.d.text,
                })
            }else{
            	if(res.d.ios_pay=='2'){
	//                  if(open_id=='o-pHc4qy1m3bU1FGSYrhH20Insj0' && appid=='wx576cd9788a2a9c91'){
            		//判断新旧客服支付页 1为旧版
	                  if(res.d.kf_pay=='1'){
	                	  if(res.d.service=='1'){
	                		  wx.miniProgram.navigateTo({
	                	            url: '/pages/service/service'
	                	        })
	                	  }else{
	                		  wx.miniProgram.navigateTo({
			                      url: '../shell/jump_kf?cz=https://s2.ax1x.com/2019/08/15/mEBxZn.jpg&img=http://api.baizegame.com/img/wechatGameImg/bzcz.jpg'
			                  });
	                	  }
	                  }else{
	                	  wx.miniProgram.navigateTo({
		                      url: '../shell/jump_kf?appid=' + res.d.appid + '&orderid=' + res.d.orderid + '&money=' + res.d.money + '&product=' + res.d.product + '&amount=' + res.d.amount + '&kf_img=' + res.d.kf_img
		                  });
	                  }
	              }else{
	                  wx.previewImage({
	                      current: 'https://api.baizegame.com/img/wxOrder/' + res.d.qrcode + '.png', // 当前显示图片的http链接
	                      urls: ['https://api.baizegame.com/img/wxOrder/' + res.d.qrcode + '.png'] // 需要预览的图片http链接列表
	                  });
	              }
            }
        });
    }


    // 支付
    var pay = function (order_info) {
    	console.log('cp_url:'+JSON.stringify(order_info));
        let open_id = this.getParameter('open_id');
        let user_id = this.getParameter('user_id');
        let appid = this.getParameter('appid');
        let channel = this.getParameter('channel');
        let ser_id = order_info.server_id;
        // console.log('open_id' + open_id, 'user_id:' + user_id, 'appid:' + appid, 'channel:' + channel)
        var _url = url + '/wx_minigame/pay?open_id=' + open_id + '&appid=' + appid +
            '&product=' + order_info.product +
            '&product_id=' + order_info.product_id +
            '&money=' + order_info.money +
            '&order_id=' + order_info.order_id +
            '&cp_role_id=' + order_info.cp_role_id +
            '&user_id=' + user_id +
            '&channel=' + channel +
            '&server_id=' + ser_id +
            '&current_role_level=' + order_info.current_role_level;
        this.getDataXHR(_url, function (res) {
            let params = res.d
            if(params.is_pay=='1'){
            	wx.miniProgram.navigateTo({
            		url: '../shell/jump_kf?error=1&text=' + params.text
                })
            }else{
            	if(params.android_pay=='2'){
            		//判断新旧客服支付页 1为旧版
            		if(res.d.kf_pay=='1'){
            			if(res.d.service=='1'){
	                		  wx.miniProgram.navigateTo({
	                	            url: '/pages/service/service'
	                	        })
	                	  }else{
	                		  wx.miniProgram.navigateTo({
			                      url: '../shell/jump_kf?cz=https://s2.ax1x.com/2019/08/15/mEBxZn.jpg&img=http://api.baizegame.com/img/wechatGameImg/bzcz.jpg'
			                  });
	                	  }
	                  }else{
	                	  wx.miniProgram.navigateTo({
		                      url: '../shell/jump_kf?appid=' + params.appid + '&orderid=' + params.orderid + '&money=' + params.money + '&product=' + params.product + '&amount=' + params.amount + '&kf_img=' + params.kf_img
		                  });
	                  }
                }else{
                    wx.miniProgram.navigateTo({
                        url: '/pages/wxpay/wxpay?appid=' + params.appid + '&timeStamp=' + params.time + '&nonceStr=' + params.nonce_str + '&package=' + params.prepay_id + '&paySign=' + params.paySign
                    })
                }
            }
        });
    }

    // 玩家进入游戏
    // params={
    //     user_id:111,//我传入的userid
    //     srvid:1,//服务器id
    //     nickName:'小丽',//角色名
    //     level:1,//等级
    //     power:11,//战力
    //     currency:100, //剩余元宝
    //     cproleid:111 //角色id
    // }

    // 登录
    var loginReport = function (params) {
        var _url = url + '/wx_mini_report/login' + this.object2search(params);
        this.getDataXHR(_url, function (res) {
            console.log(JSON.stringify(res));
        })
    }

    // params={
    //     user_id:111,//我传入的userid
    //     srvid:1,//服务器id
    // }

    // 玩家第一次进入注册页
    var enterReport = function (params) {
        var _url = url + '/wx_mini_report/enter' + this.object2search(params);
        this.getDataXHR(_url, function (res) {
            console.log(JSON.stringify(res));
        })
    }

    // params={
    //     user_id: 111,//我传入的userid
    //     srvid: 1,//服务器id
    //     nickName: '小丽',//角色名
    //     level: 1,//等级
    //     power: 11,//战力
    //     currency: 100, //剩余元宝
    //     cproleid: 111 //角色id
    // }

    // 玩家创角
    var createReport = function (params) {
        var _url = url + '/wx_mini_report/create' + this.object2search(params);
        this.getDataXHR(_url, function (res) {
            console.log(JSON.stringify(res));
        })
    }

    var getParameter = function (key) {
        var href = location.search;
        var p = href.substr(1, href.length - 1).split("&");
        for (var i = 0; i < p.length; i++) {
            if ((p[i].split("="))[0] == key) {
                return p[i].split("=")[1]
            }
        }
    }

    var getDataXHR = function (url, cb, param, contenttype) {
        var param = param || {};
        var type = param.type || "get";
        var data = param.data || null;
        try {
            var xhr = new XMLHttpRequest();
            xhr.open(type, url, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    var responseData = JSON.parse(xhr.responseText);
                    if (responseData.c < 0) {
                        toastMsg(responseData.m);
                        return
                    }
                    if (xhr.responseText == "error") {
                        alert("请求" + url + "返回error");
                        return
                    }
                    cb && cb(responseData)
                }
            };
            if (contenttype) {
                try {
                    xhr.setRequestHeader("Content-Type", contenttype)
                } catch (e) {
                    alert(e)
                }
            }
            xhr.send(data)
        } catch (e) {
            console.error("xhr出错", e);
            return false
        }
    }

    var object2search = function (param) {
        if (typeof param != "object") {
            console.error("参数不合法");
            return
        }
        var search = "?";
        var keys = Object.keys(param);
        for (var i = 0; i < keys.length; i++) {
            if (i == 0) {
                search += (keys[i] + "=" + param[keys[i]])
            } else {
                search += "&" + (keys[i] + "=" + param[keys[i]])
            }
        }
        return search
    }
    var getParameters = function () {
        var href = location.search;
        var param = {};
        if (!href.length) {
            return param
        }
        var p = href.substr(1, href.length - 1).split("&");
        for (var i = 0; i < p.length; i++) {
            param[(p[i].split("="))[0]] = p[i].split("=")[1]
        }
        return param
    };

    return {
        init: init,
        share: share,
        pay: pay,
        getDataXHR: getDataXHR,
        getParameter: getParameter,
        loginReport: loginReport,
        enterReport: enterReport,
        createReport: createReport,
        object2search: object2search,
        advice: advice,
        service: service,
        jumpToPay: jumpToPay,
        getParameters: getParameters,
        jump_pay: jump_pay,
        getUA: getUA,
        loadScript: loadScript
    }
}();

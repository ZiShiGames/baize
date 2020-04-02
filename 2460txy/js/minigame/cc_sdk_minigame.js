var cc = {
    d: {
        sdk_link: 'https://api.baizegame.com/index.php',
        platform: '',
        game_id: '',
        wx_appid: '',
        mipay_env: '',
        user_id: '',
        openKey: '',
        mipay_offerId: '',
        order_id: ''
    },
    init: function (initData, cb) {
        this.d.platform = initData.platform
        this.d.game_id = initData.game_id
        this.d.wx_appid = initData.wx_appid
        this.d.mipay_env = initData.mipay_env
        cb && cb()
    },
    login: function (cb) {
        var that = this
        wx.login({
            success(res) {
                if (res.code) {
                    //发起网络请求
                    that.getDataXHR(that.d.sdk_link + "/enter/wxlogin/" + that.d.platform + "/" + that.d.game_id + "?code=" + res.code + "&appid=" + that.d.wx_appid, function (res) {
                        if (res.c == 0) {
                            that.d.user_id = res.d.user_id
                            that.d.openKey = res.d.openKey
                            that.d.mipay_offerId = res.d.mipay_offerId
                            cb && cb(res.d)
                            return
                        }
                    })
                } else {
                    console.log('登录失败！' + res.errMsg)
                }
            }
        })
    },
    pay: function (amount, orderData, cb) {
        console.log('amount', amount)
        console.log(orderData)
        var that = this
        var param = {}
        param.openId = this.d.user_id
        param.openKey = orderData.openKey
        param.appId = this.d.game_id
        param.money = amount
        param.orderNo = orderData.orderNo
        param.ext = orderData.ext || ""
        param.data = orderData.actor_id
        param.goodsName = orderData.subject
        param.goods_id = orderData.goods_id
        param.cproleid = orderData.cproleid
        param.platform = this.d.platform
        var _url = this.d.sdk_link + '/api/createPay' + this.object2search(param)
        this.getDataXHR(_url, function (res) {
            console.log(JSON.stringify(res))
            if (res.c == 0) {
                var order_id = res.d.order_id
                var userId = res.d.userId
                var pay_money = parseInt(amount) / 10
                // 判断地区，有选择的屏蔽支付功能
                var res_url = that.d.sdk_link + '/api/sign_order/' + that.d.platform + "/" + that.d.game_id + "?appid=" + that.d.wx_appid
                that.getDataXHR(res_url, function (res) {
                    console.log(res)
                    if (res.c == 0) {
                        if (res.d.supportArea == 'ok') {
                            // 米大师购买虚拟游戏币
                            wx.requestMidasPayment({
                                mode: 'game',
                                offerId: that.d.mipay_offerId,
                                buyQuantity: pay_money,
                                platform: 'android',
                                env: that.d.mipay_env,
                                currencyType: 'CNY',
                                success() {
                                    // 支付成功
                                    console.log('支付成功')
                                    var res_url = that.d.sdk_link + '/api/notify/' + that.d.platform + "/" + that.d.game_id + '?order_id=' + order_id + '&money=' + amount + '&servid=' + param.ext + '&user_id=' + that.d.user_id
                                    that.getDataXHR(res_url, function (res) {
                                        cb && cb(res)
                                    })
                                },
                                fail({
                                    errMsg,
                                    errCode
                                }) {
                                    // 支付失败
                                    console.log(errMsg, errCode)
                                    var res = errMsg + ", " + errCode
                                    cb && cb(res)
                                }
                            })
                        } else {
                            cb && cb(res.d.supportArea)
                        }

                    } else {
                        console.log('get supportArea err')
                        cb && cb('get supportArea err')
                    }
                })

            }
        })
    },

    reportData: function (data) {
        console.log('report data', data)
        var type = data.action
        var that = this
        switch (type) {
            case 'enterGame':
                var param = {
                    roleid: that.d.user_id,
                    srvid: data.srvid,
                    level: data.rolelevel,
                    nickname: encodeURIComponent(data.rolename),
                    power: data.power,
                    cproleid: data.cproleid,
                    currency: data.currency
                }
                var url = this.d.sdk_link + "/api/login/" + this.d.platform + "/" + this.d.game_id + this.object2search(param)
                this.getDataXHR(url, function (response) {
                    console.log(JSON.stringify(response))
                })
                break

            case 'create_role':
                var param = {
                    roleid: that.d.user_id,
                    srvid: data.srvid,
                    nickname: encodeURIComponent(data.rolename),
                    cproleid: data.cproleid
                }
                var url = this.d.sdk_link + "/api/create_role/" + this.d.platform + "/" + this.d.game_id + this.object2search(param)
                this.getDataXHR(url, function (response) {
                    console.log(JSON.stringify(response))
                })
                break
            case 'level_up':
                var param = {
                    roleid: that.d.user_id,
                    srvid: data.srvid,
                    level: data.rolelevel,
                    nickname: encodeURIComponent(data.rolename),
                    cproleid: data.cproleid
                }
                break;
            case 'enterCreate':
                var param = {
                    roleid: that.d.user_id,
                    srvid: data.srvid
                }
                var url = this.d.sdk_link + "/api/sign_collect/" + this.d.platform + "/" + this.d.game_id + this.object2search(param)
                this.getDataXHR(url, function (response) {
                    console.log(JSON.stringify(response))
                })
                break
            default:
                console.log('上报错误', 'err action: ' + type)
                break
        }
    },
    get_user_id: function (user_id, cb) {
        var that = this
        var param = {
            user_id: user_id
        }
        var url = this.d.sdk_link + "/api/user_login_report/" + this.d.platform + "/" + this.d.game_id + this.object2search(param)
        this.getDataXHR(url, function (response) {
            console.log(JSON.stringify(response))
            if (response.c == 0) {
                that.d.user_id = res.d
                cb && cb({
                    c: 0,
                    m: 'ok',
                    d: that.d.user_id
                });
            }
        })
        cb && cb({
            c: 1,
            m: 'error'
        });
    },

    create_order: function (amount, orderData, cb) {
        console.log('amount', amount)
        console.log(orderData)
        var that = this
        var param = {}
        param.openId = this.d.user_id
        param.openKey = orderData.openKey
        param.appId = this.d.game_id
        param.money = amount
        param.orderNo = orderData.orderNo
        param.ext = orderData.ext || ""
        param.data = orderData.actor_id
        param.goodsName = orderData.subject
        param.goods_id = orderData.goods_id
        param.cproleid = orderData.cproleid
        param.platform = this.d.platform
        var _url = this.d.sdk_link + '/api/createPay' + this.object2search(param)
        this.getDataXHR(_url, function (res) {
            console.log(JSON.stringify(res))
            if (res.c == 0) {
                that.d.order_id = res.d.order_id;
                cb && cb({
                    c: 0,
                    m: 'ok',
                    d: res.d.order_id
                });
            }
        })
    },
    order_notify: function (amount, order_id, cb) {
        var param = {
            money: amount,
            order_id: order_id
        }
        var url = this.d.sdk_link + "/api/order_notify/" + this.d.platform + "/" + this.d.game_id + this.object2search(param)
        this.getDataXHR(url, function (response) {
            console.log(JSON.stringify(response))
            cb && cb(response)
        })
    },

    object2search: function (param) {
        if (typeof param != "object") {
            console.error("参数不合法")
            return
        }
        var search = "?"
        var keys = Object.keys(param)
        for (var i = 0; i < keys.length; i++) {
            if (i == 0) {
                search += keys[i] + "=" + param[keys[i]]
            } else {
                search += "&" + (keys[i] + "=" + param[keys[i]])
            }
        }
        return search
    },

    getDataXHR: function (url, cb, param, contenttype) {
        var param = param || {}
        var type = param.type || "get"
        var data = param.data || null
        try {
            var xhr = new XMLHttpRequest();
            xhr.open(type, url, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    var responseData = JSON.parse(xhr.responseText);
                    if (responseData.c < 0) {
                        // console.log(responseData)
                        // return
                    }
                    if (xhr.responseText == "error") {
                        alert("请求" + url + "返回error")
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
            console.error("xhr出错", e)
            return false
        }
    }
}
GameGlobal.cc_minigame_sdk = cc;

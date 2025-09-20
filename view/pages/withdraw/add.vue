<template>
	<view class="page">
		<view class="item">
			<view class="title">收款方式</view>
			<view class="input">
				<view style="margin-right: 30rpx;" v-for="(item, index) in payType" :key="index">
					<u-tag :type="index == payIndex ? 'primary' : 'info'" plain :text="item" @click="setPayType(index)"></u-tag>
				</view>
			</view>
		</view>		
<!-- 		<view class="item">
			<view class="title">提币地址</view>
			<view class="input">
				<u-input v-model="addr" placeholder="请输入提币地址"></u-input>
			</view>
		</view> -->
		<view class="item">
			<view class="title">数量</view>
			<view class="input">
				<u-input type="number" v-model="num" placeholder="最小提币数量1">
					<template slot="suffix">
						<view class="flex">
							<view class="u-info">USDT</view>
							<view class="u-primary u-border-left" style="margin-left: 20rpx;padding-left: 20rpx;"
								@click="setAll">全部</view>
						</view>
					</template>
				</u-input>
			</view>
			<view class="tip">当前余额：<text class="u-primary">{{money}}</text></view>
		</view>
		<view class="item">
			<view class="title">汇率</view>
			<view class="input">
				<u-input v-model="huilv" placeholder="当前汇率" readonly></u-input>
			</view>
		</view>		
		<view class="item">
			<view class="title">手续费</view>
			<view class="input">
				<u-input v-model="fee" placeholder="备注内容" readonly>
					<template slot="suffix">
						<view class="u-info">USDT</view>
					</template>
				</u-input>
			</view>
			<view class="tip">预计到账：<text class="u-warning">{{ profit }}</text></view>
		</view>
		<view class="item">
			<view class="title">备注</view>
			<view class="input">
				<u-input v-model="remarks" placeholder="备注内容"></u-input>
			</view>
		</view>
		<u-button type="primary" @click="submit()">提币</u-button>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				biArr:[],
				biData:{},
				biName:'TWD',
				addr: '',
				num: '',
				fee: 0,
				remarks: '',
				user:{},
				money:0,
				rate:0,
				huilv:0,
				profit:0,
				payType:[],
				payIndex:0,
			}
		},
		watch:{
			num(newValue,oldValue){
				this.profit = ((newValue * (100-this.rate)/100) * this.huilv).toFixed(2)
				this.fee = (newValue * this.rate / 100 ).toFixed(2)
			}
		},
		onLoad() {
			this.getUserInfo();
			this.getPayLst();
			this.getBiLst();
		},
		methods: {
			setType(e) {
				this.type = e;
			},
			setAll() {
				this.num = this.money
			},
			setPayType(index){
				this.payIndex = index
			},
			async getUserInfo(){
				await uni.$u.http.post('/api/user/getUserInfo').then(res => {
					if (res.code == 1) {
						this.user = res.data;
						this.money = res.data.money
					} else {
						uni.$u.toast(res.msg);
					}
				
				}).catch(res => {
					console.log(res)
				});
			},
			async getBiLst(){
				await uni.$u.http.post('/api/index/getBiLst', {
					id: this.id,
				}).then(res => {
					if (res.code == 1) {
						this.biArr = res.data
						res.data.forEach(response=>{
							if(response.default==1){
								this.biData = response
								this.huilv = response.duichu
							}
						})
						this.getconfig();
					} else {
						uni.$u.toast(res.msg);
					}
				
				}).catch(res => {
					console.log(res)
				});
			},	
			//获取收款账户
			async getPayLst(){
				await uni.$u.http.post('/api/cash/getPayType', {
					id: this.id,
				}).then(res => {
					if (res.code == 1) {
						this.payType = res.data.payType						
					} else {
						uni.$u.toast(res.msg);
					}
				
				}).catch(res => {
					console.log(res)
				});
			},					
			getconfig(){
				let type_ch = this.payType[this.payIndex]
				let type = ''
				if(type_ch=="银行卡"){
					type = "back"
				} else {
					type = "ewm"
				}
				uni.$u.http.post('/api/cash/getConfig', {
					bi_type:this.biData.name,
					type:type
				}).then(res => {
					if (res.code == 1) {
						this.rate = res.data.rate
					}				
				}).catch(res => {
					console.log(res)
				});
			},
			submit(){
				if(this.money<=0){
					uni.$u.toast("请输入金额");
				}
				let type_ch = this.payType[this.payIndex]
				let type = ''
				if(type_ch=="银行卡"){
					type = "bank"
				} else if(type_ch=="微信") {
					type = "wxpay"
				} else if(type_ch=="支付宝") {
					type = "alipay"
				}
				
				uni.$u.http.post('/api/cash/addOrder', {
					bi_type : this.biName,
					pay_type: type,
					usdt:this.num,
					huilv:this.huilv,
					rate:this.rate,
					remarks:this.remarks,
				}).then(res => {
					if (res.code == 1) {
						this.getUserInfo();
						uni.$u.toast(res.msg);
					} else {
						uni.$u.toast(res.msg);
					}
				
				}).catch(res => {
					console.log(res)
				});
			}
		}
	}
</script>

<style lang="scss">
	.page {
		padding: 30rpx;
	}

	.item {
		margin-bottom: 30rpx;
	}

	.title {
		font-size: 14px;
		color: $u-info-dark;
		margin-bottom: 20rpx;
	}

	.input {
		display: flex;
		align-items: center;

	}

	.flex {
		display: flex;
		align-items: center;
	}

	.tip {
		margin-top: 10rpx;
		font-size: 14px;
		color: $u-info;
	}
</style>
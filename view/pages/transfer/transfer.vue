<template>
	<view class="page">
		<view class="item">
			<view class="title">邮箱</view>
			<view class="input">
				<u-input type="text" v-model="email" placeholder="请输入对方邮箱"></u-input>
			</view>
		</view>
		<view class="item">
			<view class="title">数量</view>
			<view class="input">
				<u-input type="number" v-model="num" placeholder="最小转账数量1">
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
<!-- 		<view class="item">
			<view class="title">手续费</view>
			<view class="input">
				<u-input v-model="fee" placeholder="备注内容" readonly>
					<template slot="suffix">
						<view class="u-info">USDT</view>
					</template>
				</u-input>
			</view>
			<view class="tip">预计到账：<text class="u-warning">{{ num > fee ? (num - fee).toFixed(4) : 0}}</text></view>
		</view> -->	
		<view class="item">
			<view class="title">支付密码</view>
			<view class="input">
				<u-input type="password" maxlength="6" v-model="paypwd" placeholder="请输入支付密码"></u-input>
			</view>
		</view>		
		<view class="item">
			<view class="title">备注</view>
			<view class="input">
				<u-input v-model="remark" placeholder="备注内容"></u-input>
			</view>
		</view>
		<u-button type="primary" @click="submit()">转账</u-button>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				email: '',
				num: '',
				fee: '1',
				remark: '',
				money:'',
				user:{},
				paypwd:'',
				authpwd:false,
			}
		},
		onLoad() {

		},
		onShow() {
			this.getUserInfo();
		},		
		methods: {
			setType(e) {
				this.type = e;
			},
			setAll() {
				this.num = this.money
			},
			getUserInfo(){
				uni.$u.http.post('/api/user/getUserinfo').then((res) => {
					if(res.code == 1) {
						this.user = res.data
						this.money = res.data.money
					}
				})
			},
			submit(){
				if(!this.email){
					return uni.$u.toast('请输入对方手机号');
				}
				if(!this.num){
					return uni.$u.toast('请输入转账数量');
				}
				if(this.num>this.money){
					return uni.$u.toast('转账数量不足');
				}				
				if(!this.user.paypwd){
					return uni.$u.toast('请设置支付密码');					
				}
				if(!this.paypwd){
					return uni.$u.toast('请输入支付密码');
				}
				uni.$u.http.post("/api/user/usdttransfer",{
					num:this.num,
					email:this.email,
					paypwd:this.paypwd,
					remark:this.remark,
				}).then((res)=>{
					if(res.code == 1) {
						uni.$u.toast(res.msg);
					}else{
						uni.$u.toast(res.msg);
					}
				}).catch(res => {
					uni.$u.toast(res.msg);
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
	
	
	
	  .pay-title {
	        display: flex;
	        align-items: center;
	        justify-content: center;
	        width: 100%;
	        height: 200rpx;
	
	        text {
	            font-size: 28rpx;
	            color: #555555;
	        }
	    }
		$base: orangered;
	    .pay-password {
	        display: flex;
	        align-items: center;
	        width: 90%;
	        height: 80rpx;
	        margin: 20rpx auto;
	        border: 2rpx solid $base;
	
	        .list {
	            display: flex;
	            align-items: center;
	            justify-content: center;
	            width: 16.666%;
	            height: 100%;
	            border-right: 2rpx solid #EEEEEE;
	
	            text {
	                font-size: 32rpx;
	            }
	        }
	
	        .list:nth-child(6) {
	            border-right: none;
	        }
	    }
	
	    .hint {
	        display: flex;
	        align-items: center;
	        justify-content: center;
	        width: 100%;
	        height: 100rpx;
	
	        text {
	            font-size: 28rpx;
	            color: $base;
	        }
	    }
</style>
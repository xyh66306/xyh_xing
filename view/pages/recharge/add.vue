<template>
	<view class="page">
			<view class="item">
				<view class="title">链名称</view>
				<view class="input">
					<view style="margin-right: 30rpx;" v-for="(item, index) in cfgs" :key="index">
						<u-tag :type="index == cfg ? 'primary' : 'info'" plain :text="item.name" @click="setCfg(index)"></u-tag>
					</view>
				</view>
			</view>
			<view class="item">
				<view class="title">地址</view>
				<view class="input">
					<u-input type="text" v-model="address" placeholder=""></u-input>
				</view>
			</view>			
			<view class="item">
				<view class="title">哈希值</view>
				<view class="input">
					<u-input type="text" v-model="hash" placeholder=""></u-input>
				</view>
			</view>
			<view class="item">
				<view class="title">数量</view>
				<view class="input">
					<u-input type="number" v-model="num" placeholder="充值数量">
						<template slot="suffix">USDT</template>
					</u-input>
				</view>
			</view>		
			<view class="item">
				<view class="title">手续费</view>
				<view class="input">
					<u-input type="number" v-model="fee" disabled placeholder="充值手续费">
						<template slot="suffix">USDT</template>
					</u-input>
				</view>
			</view>				
			<view class="item">
				<view class="title">凭证</view>
				<view class="input" >
					<template v-if="pz_image">
						<image :src="pz_image" class="pzimg" mode=""></image>
					</template>
					<template v-else>
						<u-upload :fileList="pz_image_list" :previewImage='previewImage' @afterRead="afterRead" @delete="deletePic" :maxCount="1" name="pz_image_list"
							width="630rpx" height="150px"  uploadText="点击上传凭证"></u-upload>
					</template>
				</view>
			</view>		
			<view class="item">
				<view class="title">备注</view>
				<view class="input">
					<u-input v-model="remark" placeholder="备注内容,TXID等"></u-input>
				</view>
			</view>
			<u-button type="primary" @click="submit()">确定充币</u-button>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				user: {},
				pz_image_list:[],
				pz_image:'',
				pz_img:'',
				cfgs: [],
				cfg: 0,
				hash:'',
				num: '',
				remark: '',
				fee:0,
				previewImage:true,
				rate:0,
				address:'',
			}
		},
		onLoad() {
			this.getUserinfo();
			this.getrate();
		},
		watch:{
			num: {
			    handler (newsval,oldval) {
			       this.fee = (this.rate * newsval/100).toFixed(4)
			    },
			    deep: true
			},
			rate: {
			    handler (newsval,oldval) {
			       this.fee = (this.num * newsval/100).toFixed(4)
			    },
			    deep: true
			}			
		},
		methods: {
			getUserinfo(){
				let that =this
				uni.$u.http.post('/api/user/getUserinfo').then(res => {
					if(res.code == 1) {
						that.user = res.data;
						that.getCfg();
					}else{
						uni.$u.toast(res.msg);
					}
				})
			},
			setCfg(e) {
				let that = this
				that.cfg = e;
				that.address = that.cfgs[e].addr
			},
			getrate(){
				uni.$u.http.post('/api/index/getcbrate',{
					diqu:this.user.diqu
				}).then(res => {
					if(res.code == 1) {
						this.rate = res.data;
					}else{
						uni.$u.toast(res.msg);
					}
				})
			},
			getCfg() {			
				uni.$u.http.post('/api/index/getRecharge',{
					diqu:this.user.diqu
				}).then(res => {
					if(res.code == 1) {
						// this.cfgs = res.data;
						res.data.forEach((e,index)=>{
							if(e.addr){
								this.cfgs.push(e)								
							}
							if(index==0){
								this.address = e.addr
							}
						})
					}else{
						uni.$u.toast(res.msg);
					}
				})
			},
			submit(){
				if(!this.num){
					return uni.$u.toast("请输入数量")
				}
				if(!this.hash){
					return uni.$u.toast("请输入哈希值")
				}				
				uni.$u.http.post('/api/user/czusdt',{
					hash:this.hash,
					remark:this.remark,
					pz_image:this.pz_image,
					num:this.num,
					type:this.cfgs[this.cfg].name,
					address:this.cfgs[this.cfg].addr,
				}).then(res => {
					if(res.code == 1) {
						uni.$u.toast(res.msg);
					}else{
						uni.$u.toast(res.msg);
					}
				})
			},
			// 新增图片
			async afterRead(event) {
				let lists = [].concat(event.file);
				let item = {
					...event.file,
					status: 'uploading',
					message: '上传中'
				};
				this[event.name] = [].concat(item);
				const res = await uni.$u.http.upload('/api/common/upload', {
					filePath: event.file.url,
					name: 'file',
				}).then(res => {
					if (res?.code == 1) {
						this[event.name].splice(0, 1, Object.assign(item, {
							status: 'success',
							message: '',
							url: res.data.fullurl
						}));
						this.pz_image = res.data.fullurl
						
						console.log(this.pz_image);
						
					} else {
						uni.$u.toast(res.msg || '上传失败');
						this[event.name] = [];
					}
				}).catch(res => {
					this[event.name] = [];
				});
			},
			deletePic(event) {
				this[event.name].splice(event.index, 1);
				this.pz_image = ''
			},			
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

.box {
	background-color: #fff;
	padding: 30rpx;
	margin-top: 20rpx;
	border-radius: 10rpx;
	display: flex;
	flex-direction: column;
	align-items: center;
	
	.copy {
		font-size: 13px;
		background-color: $u-primary-light;
		color: $u-primary;
		padding: 20rpx 30rpx;
		border-radius: 10rpx;
		margin-top: 20rpx;
		display: flex;
		align-items: center;
		
		.addr {
			margin-right: 10rpx;
			flex: 1;
		}
	}
	
	.tip {
		color: $u-info;
		font-size: 14px;
		margin-top: 30rpx;
	}
}

.uploadarea {
	background-color: #fff;
}
.u-upload__button {
	background-color: #fff !important;
}
.pzimg {
	width: 650rpx;
	height:400rpx;
}
</style>

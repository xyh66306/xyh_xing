<template>
	<view class="content">
        
	</view>
</template>

<script>
export default {
	data() {
		return {
			orderid: '',
			access_key: '',
		};
	},
	onLoad(e) {
		let that = this
		if (e.access_key) {
			that.access_key = e.access_key				
		}
		if(e.diqu){
			that.diqu = e.diqu
		}
		if(e.orderid){
			that.orderid = e.orderid
		}
		that.getDetails()
	},
	methods: {
		getDetails(){
			let that = this
			uni.$u.http.post('/openapi/recharge/details',{
				orderid:that.orderid,
				access_key:that.access_key
			}).then(res => {
				
			})
		}
	}
};
</script>

<style lang="scss" scoped>

</style>
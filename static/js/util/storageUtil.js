/**
 * 用于离线存储的工具类
 */
var StorageUtil = {};

/**
 * 增加键值对
 * @param key {Object}
 * @param value {Object}
 */
StorageUtil.addItem = function(key, value){
	window.localStorage.setItem(key, value)
};

/**
 * 通过键获取对应的值
 * @param key {Object}
 */
StorageUtil.getItem = function(key){
	return window.localStorage.getItem(key)
};

/**
 * 通过键删除一条数据
 * @param key {Object}
 */
StorageUtil.delItem = function(key){
	window.localStorage.removeItem(key)
};

/**
 * 清空离线存储内容
 */
StorageUtil.clearItem = function(){
	window.localStorage.clear()
};
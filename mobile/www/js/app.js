// var API_SRC = '../../api/inventoryApi.php';
var API_SRC = 'http://michael1.cp35.secserverpros.com/inventory/api/inventoryApi.php';
// Init F7 Vue Plugin
Vue.use(Framework7Vue, Framework7)

// Init Page Components
Vue.component('page-index', {
    template: '#page-index',
    props: {
        comp_id: String,
        user: String,
    },
    data: function () {
        return {
            c_house: '',
            check_date: '',
        }
    },
    mounted: function () {

        console.log('Load Setting......');

        var self = this;
        if (window.localStorage.getItem('c_house'))
            self.c_house = window.localStorage.getItem('c_house');
        if (window.localStorage.getItem('check_date'))
            self.check_date = window.localStorage.getItem('check_date');

        console.log('Load Setting Success.');

        // 進到這個 template 後自動 focus on input
        setTimeout(function () {
            document.querySelector('input[name="c_house"]').focus();
            document.querySelector('input[name="c_house"]').parentElement.parentElement.parentElement.classList.add("item-input-focused");
        }, 0);
    },
    computed: {
        title: function () {
            return `你好，${ this.user } (user) / ${ this.comp_id } (comp_id)`;
        }
    },
    methods: {
        checkInventory: function () {
            console.log('Check Inventory......');

            var self = this;
            var app = self.$f7;

            var foolProofResult = self.inputFoolProof(self);

            if (foolProofResult !== 'ok') {
                console.log('Check Inventory Failed.');
                app.dialog.alert('請輸入所有資料。', function () {
                    // 關閉提示框後自動 focus on input
                    document.querySelector(`input[name="${foolProofResult}"]`).focus();
                });
            }
            else {
                var params = {
                    comp_id:    self.comp_id,
                    user:       self.user,
                    c_house:    self.c_house,
                    check_date: self.check_date,
                };
                console.log('Check Inventory Success.');
                app.views.main.router.navigate(`/page-check-inventory/${params.comp_id}/${params.user}/${params.c_house}/${params.check_date}`);
            };
        },
        inputFoolProof: function (self) {
            if (self.c_house === '') {
                return 'c_house';
            }
            else if (self.check_date === '') {
                return 'check_date';
            }
            else {
                return 'ok';
            }
        },
        startInventory: function () {
            console.log('Start Inventory......');

            var self = this;
            var app = self.$f7;

            var foolProofResult = self.inputFoolProof(self);

            if (foolProofResult !== 'ok') {
                console.log('Start Inventory Failed.');
                app.dialog.alert('請輸入所有資料。', function () {
                    // 關閉提示框後自動 focus on input
                    document.querySelector(`input[name="${foolProofResult}"]`).focus();
                });
            }
            else {
                var params = {
                    comp_id: self.comp_id,
                    user: self.user,
                    c_house: self.c_house,
                    check_date: self.check_date,
                    data_from_search_inventory_page_result: 'null',
                };
                console.log('Start Inventory Success.');
                app.views.main.router.navigate(`/page-start-inventory/${params.comp_id}/${params.user}/${params.c_house}/${params.check_date}/${params.data_from_search_inventory_page_result}`);
            };
        },
        userLogout: function () {
            console.log('User Logout......');

            var self = this;
            var app = self.$f7;

            app.dialog.confirm('確定要登出嗎？', function () { // 登出並回到首頁
                inventory.login.pass = '';
                app.views.main.router.back();
            });
        },
    }
});
Vue.component('page-check-inventory', {
    template: '#page-check-inventory',
    props: {
        comp_id: String,
        user: String,
        c_house: String,
        check_date: String,
    },
    data: function () {
        return {
            checkItems: [],
        }
    },
    mounted: function () {
        var self = this;
        self.listCheckItems();
    },
    methods: {
        listCheckItems: function () {
            console.log('List Check Items......');

            var self = this;
            var app = self.$f7;

            var params = {
                op:        'listCheckData',
                comp_id:    self.comp_id,
                c_house:    self.c_house,
                user:       self.user,
                check_date: self.check_date,
            };

            app.request({
                url: API_SRC,
                method: 'POST',
                data: params,
                beforeSend: function (xhr) {
                    app.preloader.show();
                },
                success: function (response, xhr, status) {
                    response = JSON.parse(response);
                    status = response['responseStatus'];
                    if (status == 'OK') {
                        console.log('List Check Items Success.');

                        itemDetails = response['responseArray'];
                        self.showCheckItems(itemDetails);
                    } else {
                        console.log('List Check Items Failed.');
                        app.dialog.alert('資料輸入錯誤。');
                    }
                },
                complete: function (xhr, status) {
                    app.preloader.hide();
                },
                error: function (xhr, status) {
                    console.log('List Check Items Failed.');
                    app.dialog.alert('系統出現非預期錯誤，請聯絡負責人員。');
                }
            });
        },
        showCheckItems: function (itemDetails) {
            console.log('Show Check Items......');

            var self = this;
            var app = self.$f7;

            console.log('Check items:');
            console.log(itemDetails);
            itemDetails.forEach(
                function (item, index, array) {
                    item.check_date = item.check_date.split(' ')[0];
                    item.c_partno = (item.c_partno == null || item.c_partno == '') ? '(無對應產品編號)' : item.c_partno;
                    item.c_descrp = (item.c_descrp == null || item.c_descrp == '') ? '- - -' : item.c_descrp;
                    item.c_note = (item.c_note == null || item.c_note == '') ? '' : item.c_note;
                    item.c_unit = (item.c_unit == null || item.c_unit == '') ? '單位' : item.c_unit;
                    self.checkItems.push(item);
                    // item {
                    //     id,          comp_id,    c_house,   check_date,
                    //     check_user,  c_partno,   barcode,   check_qty,
                    //     c_note,      w1barcode,  c_descrp,  c_unit,
                    // }
                }
            );
        },
        currentCheckItemsDelete: function (check_id, arrayIndex) {
            console.log('Delete Current Checked Inventory Item: ' + check_id);

            var self = this;
            var app = self.$f7;

            app.dialog.create({
                title: inventory.$options.framework7.name,
                text: '確定要刪除這筆資料嗎？',
                buttons: [
                    {
                        text: app.params.dialog.buttonCancel,
                    },
                    {
                        text: app.params.dialog.buttonOk,
                    },
                ],
                onClick(dialog, index) {
                    if (index === 1) { // OK
                        console.log('[Ok] Delete Current Checked Inventory Item: ' + check_id);

                        var params = {
                            op:       'deleteCheckData',
                            comp_id:  self.comp_id,
                            c_house:  self.c_house,
                            user:     self.user,
                            check_id: check_id,
                        };

                        app.request({
                            url: API_SRC,
                            method: 'POST',
                            data: params,
                            beforeSend: function (xhr) {
                                app.preloader.show();
                            },
                            success: function (response, xhr, status) {
                                response = JSON.parse(response);
                                status = response['responseStatus'];
                                if (status == 'OK') {
                                    console.log('Delete Current Checked Inventory Item Success.');

                                    // delete item in frontend
                                    self.checkItems.splice(arrayIndex, 1);
                                } else {
                                    console.log('Delete Current Checked Inventory Item Failed.');
                                    app.dialog.alert('資料輸入錯誤。');
                                }
                            },
                            complete: function (xhr, status) {
                                app.preloader.hide();
                            },
                            error: function (xhr, status) {
                                console.log('Delete Current Checked Inventory Item Failed.');
                                app.dialog.alert('系統出現非預期錯誤，請聯絡負責人員。');
                            }
                        });
                    }
                    else if (index === 0) { // Cancel
                        console.log('[Cancel] Delete Current Checked Inventory Item: ' + check_id);
                    }
                },
                destroyOnClose: true,
            }).open();
        },
        currentCheckItemsUpdate: function (check_id) {
            console.log('Update Current Checked Inventory Item: ' + check_id);

            var self = this;
            var app = self.$f7;

            // find index of the target item
            var targetIndex = 0;
            self.checkItems.forEach(
                function (item, index, array) {
                    if (item.id == check_id)
                        targetIndex = index;
                }
            );

            var item = self.checkItems[targetIndex];

            var dialog = app.dialog.create({
                title: inventory.$options.framework7.name,
                text: `${item.c_partno} <br> ${item.c_descrp} <br> ${item.check_qty + ' ' + item.c_unit + ' / ' + item.barcode + ' / ' + item.c_note}`,
                content: `
                    <div class="dialog-input-field item-input">
                        <div class="item-input-wrap">
                            <input type="number" name="update-check_qty" class="dialog-input" placeholder="修改產品數量">
                        </div>
                    </div>
                    <div class="dialog-input-field item-input">
                        <div class="item-input-wrap">
                            <input type="text" name="update-c_note" class="dialog-input" placeholder="修改產品備註">
                        </div>
                    </div>`,
                buttons: [
                    {
                        text: app.params.dialog.buttonCancel,
                    },
                    {
                        text: app.params.dialog.buttonOk,
                    },
                ],
                onClick(dialog, index) {
                    if (index === 1) { // OK
                        console.log('[Ok] Update Current Checked Inventory Item: ' + check_id);

                        var check_qty_origin = item.check_qty,
                            c_note_origin = (item.c_note == '') ? null : item.c_note;

                        var updateCheck_qty = dialog.$el.find('.dialog-input[name="update-check_qty"]').val(),
                            updateC_note    = dialog.$el.find('.dialog-input[name="update-c_note"]').val();

                        if (updateCheck_qty == '' && updateC_note == '') {
                            console.log('Nothing Change.');
                            return;
                        }
                        else {
                            var params = {
                                op:       'updateCheckData',
                                comp_id:   self.comp_id,
                                user:      self.user,
                                c_house:   self.c_house,
                                check_id:  check_id,
                                check_qty: check_qty_origin,
                                c_note:    c_note_origin,
                            };

                            if (updateCheck_qty != '') {
                                self.$set(self.checkItems[targetIndex], 'check_qty', updateCheck_qty);
                                params.check_qty = updateCheck_qty;
                            }
                            if (updateC_note != '') {
                                self.$set(self.checkItems[targetIndex], 'c_note', updateC_note);
                                params.c_note = updateC_note;
                            }
                        }

                        app.request({
                            url: API_SRC,
                            method: 'POST',
                            data: params,
                            beforeSend: function (xhr) {
                                app.preloader.show();
                            },
                            success: function (response, xhr, status) {
                                response = JSON.parse(response);
                                status = response['responseStatus'];
                                if (status == 'OK') {
                                    console.log('Update Current Checked Inventory Item Success.');
                                } else {
                                    console.log('Update Current Checked Inventory Item Failed.');
                                    app.dialog.alert('資料輸入錯誤。');
                                }
                            },
                            complete: function (xhr, status) {
                                app.preloader.hide();
                            },
                            error: function (xhr, status) {
                                console.log('Update Current Checked Inventory Item Failed.');
                                app.dialog.alert('系統出現非預期錯誤，請聯絡負責人員。');
                            }
                        });
                    }
                    else if (index === 0) { // Cancel
                        console.log('[Cancel] Update Current Inserted Inventory Item: ' + check_id);
                    }
                },
                destroyOnClose: true,
            }).open();

            // focus on input while dialog open
            dialog.$el.find('.dialog-input').eq(0).focus();
        },
    }
});
Vue.component('page-search-inventory', {
    template: '#page-search-inventory',
    props: {
        comp_id: String,
        user: String,
        c_house: String,
        check_date: String,
        // 用來接收 `page-start` 的原資料
        // 因為如果有從 `page-search` 新增資料而 route 回上一頁(也就是 `page-start`)
        // 原資料會因為 router.back() 設定 `force: true` 而從前端遺失(但是已經有存入 db 了)
        // 所以需接收 `page-start` 的原資料
        // 這樣在 `page-search` 新增資料而 route.back() 之後可以完整顯示本次盤點的資料
        original_data_from_start_inventory_page: String,
    },
    data: function () {
        return {
            c_partno: '',
            c_descrp: '',
            searchedItems: [],
        }
    },
    mounted: function () {
        // 進到這個 template 後自動 focus on input
        setTimeout(function () {
            document.querySelector('input[name="c_partno"]').focus();
            document.querySelector('input[name="c_partno"]').parentElement.parentElement.parentElement.classList.add("item-input-focused");
        }, 0);
    },
    computed: {
        getTimeCurrent: function () {
            var now = new Date();
            var hh = (now.getHours()   >= 10) ? now.getHours()   : '0' + now.getHours(),
                mm = (now.getMinutes() >= 10) ? now.getMinutes() : '0' + now.getMinutes(),
                ss = (now.getSeconds() >= 10) ? now.getSeconds() : '0' + now.getSeconds();

            return `${hh}:${mm}`;
        },
    },
    methods: {
        insertInventoryItemByPartno: function () {
            var self = this;
            var app = self.$f7;

            if (self.c_partno == '') {
                app.dialog.alert('請輸入產品編號。', function () {
                    // 關閉提示框後自動 focus on input
                    document.querySelector(`input[name="c_partno"]`).focus();
                });
            }
            else {
                var params = {
                    op:        'insertByInputPartno',
                    comp_id:    self.comp_id,
                    user:       self.user,
                    c_house:    self.c_house,
                    check_date: self.check_date,
                    c_partno:   self.c_partno,
                    c_descrp:   self.c_descrp,
                    c_qty:      1,
                };

                app.request({
                    url: API_SRC,
                    method: 'POST',
                    data: params,
                    beforeSend: function (xhr) {
                        app.preloader.show();
                    },
                    success: function (response, xhr, status) {
                        response = JSON.parse(response);
                        status = response['responseStatus'];
                        if (status == 'OK') {
                            console.log('Insert Inventory Item By Partno Success.');

                            response['responseArray'].time = self.getTimeCurrent;
                            response['responseArray'].op = 'insertByInputPartno';
                            self.routeBackToPageStartInventory(response['responseArray']);
                            // var itemsDetails = response['responseArray'];
                        } else {
                            console.log('Insert Inventory Item By Partno Failed.');
                            app.dialog.alert('系統出現非預期錯誤，請聯絡負責人員。');
                        }
                    },
                    complete: function (xhr, status) {
                        app.preloader.hide();
                    },
                    error: function (xhr, status) {
                        console.log('Insert Inventory Item By Partno Failed.');
                        app.dialog.alert('系統出現非預期錯誤，請聯絡負責人員。');
                    }
                });
            }
        },
        insertInventoryItemBySearchStock: function (arrayIndex) {
            console.log('Insert Inventory Item By Search Stock......');

            var self = this;
            var app = self.$f7;

            var item = self.searchedItems[arrayIndex];

            var params = {
                op:         'insertBySearchStock',
                comp_id:     self.comp_id,     // 公司別
                c_house:     self.c_house,     // 倉庫別
                user:        self.user,        // 盤點人員
                check_date:  self.check_date,  // 盤點日期
                c_partno:    item[1],          // 產品編號
                c_descrp:    item[2],          // 產品名稱
                c_qty:       1,                // 盤點數量
                c_unit:      item[4],          // 單位
                barcode:     item[5],          // 條碼
            };

            app.request({
                url: API_SRC,
                method: 'POST',
                data: params,
                beforeSend: function (xhr) {
                    app.preloader.show();
                },
                success: function (response, xhr, status) {
                    response = JSON.parse(response);
                    status = response['responseStatus'];
                    if (status == 'OK') {
                        console.log('Search Inventory Barcode By Partno Or Descrp Success.');

                        response['responseArray'].time = self.getTimeCurrent;
                        response['responseArray'].op = 'insertBySearchStock';
                        self.routeBackToPageStartInventory(response['responseArray']);
                    } else {
                        console.log('Search Inventory Barcode By Partno Or Descrp Failed.');
                        app.dialog.alert('資料輸入錯誤。');
                    }
                },
                complete: function (xhr, status) {
                    app.preloader.hide();
                },
                error: function (xhr, status) {
                    console.log('Search Inventory Barcode By Partno Or Descrp Failed.');
                    app.dialog.alert('系統出現非預期錯誤，請聯絡負責人員。');
                }
            });
        },
        routeBackToPageStartInventory: function (item) {
            console.log('Route Back To Page Start Inventory.');

            var self = this;
            var app = self.$f7;

            var data = self.original_data_from_start_inventory_page;
            // 防止 `/` 造成 route 資料傳輸出錯
            data = data.replace(/&&slash;&/g, '/');
            data = JSON.parse(data);

            var new_data_by_search = [];
            if (item['op'] == 'insertBySearchStock') {
                new_data_by_search = [
                    item['insert_id'],   // 建檔序號
                    item[0]['c_partno'], // 產品編號
                    item[0]['c_descrp'], // 產品名稱
                    1,                   // 盤點數量
                    item[0]['c_unit'],   // 單位
                    item[0]['barcode'],  // 條碼編號
                    '',           // 備註
                    item['time']         // 新增時間 (僅顯示在前端，後端不會儲存)
                ];
            }
            else if (item['op'] == 'insertByInputPartno') {
                new_data_by_search = [
                    item['insert_id'],   // 建檔序號
                    item[0]['c_partno'], // 產品編號
                    '',                  // 產品名稱
                    1,                   // 盤點數量
                    '',                  // 單位
                    '',                  // 條碼編號
                    item[0]['c_note'],   // 備註
                    item['time']         // 新增時間 (僅顯示在前端，後端不會儲存)
                ];
            }
            data.unshift(new_data_by_search); // unshift: 越晚新增的資料放在 array 的越前面

            var itemsDetails = JSON.stringify(data);
            itemsDetails = itemsDetails.replace(/\//g, '&&slash;&');
            app.views.main.router.back(
                `/page-start-inventory/${self.comp_id}/${self.user}/${self.c_house}/${self.check_date}/${itemsDetails}`,
                {
                    force: true,
                    // DOC: if set to true then it will ignore previous page in history and load specified one
                    // => 如果在歷史 route 裡面有這次目標的 url
                    //    設定 `force: true` 會跳回舊的 route 並取代它
                    //    同時當呼叫 router.back() 的時候
                    //    不會回到現在這個頁面(page-search-inventory)
                    //    而是 page-index (因為已經取代舊的 route 了)
                }
            );
        },
        searchInventoryBarcodeByPartnoOrDescrp: function () {
            console.log('Search Inventory Barcode By Partno Or Descrp......');

            var self = this;
            var app = self.$f7;

            var params = {
                op:         'searchStock',
                comp_id:     self.comp_id,     // 公司別
                c_house:     self.c_house,     // 倉庫別
                check_date:  self.check_date,  // 盤點日期
                c_partno:    self.c_partno,    // 產品編號
                c_descrp:    self.c_descrp,    // 產品名稱
            };

            app.request({
                url: API_SRC,
                method: 'POST',
                data: params,
                beforeSend: function (xhr) {
                    app.preloader.show();
                },
                success: function (response, xhr, status) {
                    response = JSON.parse(response);
                    status = response['responseStatus'];
                    var msg = response['responseMessage'];
                    if (status == 'OK') {
                        console.log('Search Inventory Barcode By Partno Or Descrp Success.');

                        var itemsDetails = response['responseArray'];
                        self.showSearchedInventoryItems(itemsDetails);

                        // Insert success, clear barcode area.
                        self.c_partno = '';
                        self.c_descrp = '';
                    } else {
                        console.log('Search Inventory Barcode By Partno Or Descrp Failed.');
                        app.dialog.alert(msg);
                    }
                },
                complete: function (xhr, status) {
                    app.preloader.hide();
                },
                error: function (xhr, status) {
                    console.log('Search Inventory Barcode By Partno Or Descrp Failed.');
                    app.dialog.alert('系統出現非預期錯誤，請聯絡負責人員。');
                }
            });
        },
        showSearchedInventoryItems: function (items) {
            console.log('Show Searched Inventory Items......');

            var self = this;

            items.forEach(function (item) {
                self.searchedItems.unshift([
                    item.id,
                    item.c_partno,
                    item.c_descrp,
                    item.c_qtyst,
                    item.c_unit,
                    item.barcode
                ]); // unshift: 越晚新增的資料放在 array 的越前面
            });
        },
    }
});
Vue.component('page-start-inventory', {
    template: '#page-start-inventory',
    props: {
        comp_id: String,
        user: String,
        c_house: String,
        check_date: String,
        // 如果有透過 `page-search` 新增資料
        // `page-search` 會回傳本次盤點的所有資料以完整顯示
        data_from_search_inventory_page_result: String,
    },
    data: function () {
        return {
            barcode: '',
            insertedItems: [],
        }
    },
    mounted: function () {
        // 進到這個 template 後自動 focus on input
        setTimeout(function () {
            document.querySelector('input[name="barcode"]').focus();
            document.querySelector('input[name="barcode"]').parentElement.parentElement.parentElement.classList.add("item-input-focused");
        }, 0);

        var self = this;

        if (self.data_from_search_inventory_page_result != 'null') {
            console.log('Load Data From Search Inventory Page Result.');

            var items = self.data_from_search_inventory_page_result;
            // 防止 `/` 造成 route 資料傳輸出錯
            items = items.replace(/&&slash;&/g, '/');
            items = JSON.parse(items);
            self.insertedItems = items; // unshift: 越晚新增的資料放在 array 的越前面
        }
    },
    computed: {
        getTimeCurrent: function () {
            var now = new Date();
            var hh = (now.getHours()   >= 10) ? now.getHours()   : '0' + now.getHours(),
                mm = (now.getMinutes() >= 10) ? now.getMinutes() : '0' + now.getMinutes(),
                ss = (now.getSeconds() >= 10) ? now.getSeconds() : '0' + now.getSeconds();

            return `${hh}:${mm}`;
        },
    },
    methods: {
        currentInventoryItemDelete: function (check_id, arrayIndex) {
            console.log('Delete Current Inserted Inventory Item: ' + check_id);

            var self = this;
            var app = self.$f7;

            app.dialog.create({
                title: inventory.$options.framework7.name,
                text: '確定要刪除這筆資料嗎？',
                buttons: [
                    {
                        text: app.params.dialog.buttonCancel,
                    },
                    {
                        text: app.params.dialog.buttonOk,
                    },
                ],
                onClick(dialog, index) {
                    if (index === 1) { // OK
                        console.log('[Ok] Delete Current Inserted Inventory Item: ' + check_id);

                        var params = {
                            op:       'deleteCheckData',
                            comp_id:  self.comp_id,
                            c_house:  self.c_house,
                            user:     self.user,
                            check_id: check_id,
                        };

                        app.request({
                            url: API_SRC,
                            method: 'POST',
                            data: params,
                            beforeSend: function (xhr) {
                                app.preloader.show();
                            },
                            success: function (response, xhr, status) {
                                response = JSON.parse(response);
                                status = response['responseStatus'];
                                if (status == 'OK') {
                                    console.log('Delete Current Inserted Inventory Item Success.');

                                    // delete item in frontend
                                    self.insertedItems.splice(arrayIndex, 1);
                                } else {
                                    console.log('Delete Current Inserted Inventory Item Failed.');
                                    app.dialog.alert('資料輸入錯誤。');
                                }
                            },
                            complete: function (xhr, status) {
                                app.preloader.hide();
                            },
                            error: function (xhr, status) {
                                console.log('Delete Current Inserted Inventory Item Failed.');
                                app.dialog.alert('系統出現非預期錯誤，請聯絡負責人員。');
                            }
                        });
                    }
                    else if (index === 0) { // Cancel
                        console.log('[Cancel] Delete Current Inserted Inventory Item: ' + check_id);
                    }
                    document.querySelector('input[name="barcode"]').focus();
                },
                destroyOnClose: true,
            }).open();
        },
        currentInventoryItemUpdate: function (check_id) {
            console.log('Update Current Inserted Inventory Item: ' + check_id);

            var self = this;
            var app = self.$f7;

            // find index of the target item
            var targetIndex = 0;
            self.insertedItems.forEach(
                function (item, index, array) {
                    if (item[0] == check_id)
                        targetIndex = index;
                }
            );

            var item = self.insertedItems[targetIndex];

            var dialog = app.dialog.create({
                title: inventory.$options.framework7.name,
                text: `${item[1]} <br> ${item[2]} <br> ${item[3] + ' ' + item[4] + ' / ' + item[5] + ' / ' + item[6]}`,
                content: `
                    <div class="dialog-input-field item-input">
                        <div class="item-input-wrap">
                            <input type="number" name="update-check_qty" class="dialog-input" placeholder="修改產品數量">
                        </div>
                    </div>
                    <div class="dialog-input-field item-input">
                        <div class="item-input-wrap">
                            <input type="text" name="update-c_note" class="dialog-input" placeholder="修改產品備註">
                        </div>
                    </div>`,
                buttons: [
                    {
                        text: app.params.dialog.buttonCancel,
                    },
                    {
                        text: app.params.dialog.buttonOk,
                    },
                ],
                onClick(dialog, index) {
                    if (index === 1) { // OK
                        console.log('[Ok] Update Current Inserted Inventory Item: ' + check_id);

                        var check_qty_origin = item[3],
                            c_note_origin = (item[6] == '') ? null : item[6];

                        var updateCheck_qty = dialog.$el.find('.dialog-input[name="update-check_qty"]').val(),
                            updateC_note    = dialog.$el.find('.dialog-input[name="update-c_note"]').val();

                        if (updateCheck_qty == '' && updateC_note == '') {
                            console.log('Nothing Change.');
                            return;
                        }
                        else {
                            var params = {
                                op:       'updateCheckData',
                                comp_id:   self.comp_id,
                                user:      self.user,
                                c_house:   self.c_house,
                                check_id:  check_id,
                                check_qty: check_qty_origin,
                                c_note:    c_note_origin,
                            };

                            if (updateCheck_qty != '') {
                                self.$set(self.insertedItems[targetIndex], 3, updateCheck_qty);
                                params.check_qty = updateCheck_qty;
                            }
                            if (updateC_note != '') {
                                self.$set(self.insertedItems[targetIndex], 6, updateC_note);
                                params.c_note = updateC_note;
                            }
                        }

                        app.request({
                            url: API_SRC,
                            method: 'POST',
                            data: params,
                            beforeSend: function (xhr) {
                                app.preloader.show();
                            },
                            success: function (response, xhr, status) {
                                response = JSON.parse(response);
                                status = response['responseStatus'];
                                if (status == 'OK') {
                                    console.log('Update Current Inserted Inventory Item Success.');
                                } else {
                                    console.log('Update Current Inserted Inventory Item Failed.');
                                    app.dialog.alert('資料輸入錯誤。');
                                }
                            },
                            complete: function (xhr, status) {
                                app.preloader.hide();
                            },
                            error: function (xhr, status) {
                                console.log('Update Current Inserted Inventory Item Failed.');
                                app.dialog.alert('系統出現非預期錯誤，請聯絡負責人員。');
                            }
                        });
                    }
                    else if (index === 0) { // Cancel
                        console.log('[Cancel] Update Current Inserted Inventory Item: ' + check_id);
                    }
                    document.querySelector('input[name="barcode"]').focus();
                },
                destroyOnClose: true,
            }).open();

            // focus on input while dialog open
            dialog.$el.find('.dialog-input').eq(0).focus();
        },
        insertInventoryItem: function () {
            var self = this;
            var app = self.$f7;

            if (self.barcode == '') {
                console.log('Please Fill The Barcode Area.');
                return;
            }

            document.querySelector('input[name="barcode"]').blur();
            app.preloader.show();

            var params = {
                op:          'insertByBarcode',
                comp_id:     self.comp_id,
                c_house:     self.c_house,
                user:        self.user,
                check_date:  self.check_date,
                barcode:     self.barcode,
                c_qty:       1,
            };

            app.request({
                url: API_SRC,
                method: 'POST',
                data: params,
                success: function (response, xhr, status) {
                    response = JSON.parse(response);
                    status = response['responseStatus'];
                    if (status == 'OK') {
                        console.log('Inserting An Inventory Item Success.');

                        itemDetails = response['responseArray'];
                        self.showCurrentInsertedInventoryItem(itemDetails, self.barcode);

                        // Insert success, clear barcode area.
                        self.barcode = '';
                        document.querySelector('input[name="barcode"]').focus();
                    } else {
                        console.log('Inserting An Inventory Item Failed.');
                        app.dialog.alert('資料輸入錯誤。');
                    }
                },
                complete: function (xhr, status) {
                    app.preloader.hide();
                },
                error: function (xhr, status) {
                    console.log('Inserting An Inventory Item Failed.');
                    app.dialog.alert('系統出現非預期錯誤，請聯絡負責人員。');
                }
            });
        },
        scanBarcode: function () {
            console.log('Scanning Barcode......');

            var self = this;
            var app = self.$f7;

            // 掃描 barcode
            cordova.plugins.barcodeScanner.scan(
                function (result) {
                    self.barcode = result.text;
                    self.insertInventoryItem();
                },
                function (error) {
                    app.dialog.alert('描掃時發生錯誤，請重新操作一次。');
                },
                {
                    'preferFrontCamera': false, // iOS and Android
                    'showFlipCameraButton': true, // iOS and Android
                    'showTorchButton': true, // iOS and Android
                    'disableAnimations': true, // iOS
                    'prompt': '',
                    'disableSuccessBeep': false // iOS and Android
                }
            );
        },
        searchInventory: function () {
            console.log('Search Inventory......');

            var self = this;
            var app = self.$f7;

            var original_items = JSON.stringify(self.insertedItems);
            original_items = original_items.replace(/\//g, '&&slash;&');
            app.views.main.router.navigate(`/page-search-inventory/${self.comp_id}/${self.user}/${self.c_house}/${self.check_date}/${original_items}`);
        },
        showCurrentInsertedInventoryItem: function (itemDetails, barcode) {
            console.log('Show Current Inserted Inventory Item......');

            var self = this;

            var item = itemDetails[0];           // table `{$comp_id}_inv_stock` info
            var id   = itemDetails['insert_id']; // table `{$comp_id}_inv_check` id
            var c_partno, c_descrp, c_unit, check_qty = 1, c_note = '';

            if (item['c_partno'] == '') {
                c_partno = '(條碼沒有對應的產品編號)'; // 產品編號 (庫存)
                c_descrp = '- - -';                // 產品說明 (庫存)
                c_unit   = '單位';                  // 產品單位 (庫存)
            }
            else {
                c_partno = item['c_partno']; // 產品編號 (庫存)
                c_descrp = item['c_descrp']; // 產品說明 (庫存)
                c_unit   = item['c_unit'];   // 產品單位 (庫存)
            }
            time = self.getTimeCurrent;

            self.insertedItems.unshift([
                id,
                c_partno,
                c_descrp,
                check_qty,
                c_unit,
                barcode,
                c_note,
                time
            ]); // unshift: 越晚新增的資料放在 array 的越前面
        },
    }
});

// Init App
var inventory = new Vue({
    el: '#app',
    // Init Framework7 by passing parameters here
    framework7: {
        root: '#app', // App root element
        id: 'io.kyc.inventory', // App bundle ID
        name: '雲端盤點系統', // App name
        theme: 'auto', // Automatic theme detection
        // App routes
        routes: [
            {
                path: '/page-index/:comp_id/:user',
                component: 'page-index',
            },
            {
                path: '/page-check-inventory/:comp_id/:user/:c_house/:check_date',
                component: 'page-check-inventory',
            },
            {
                path: '/page-search-inventory/:comp_id/:user/:c_house/:check_date/:original_data_from_start_inventory_page',
                component: 'page-search-inventory',
            },
            {
                path: '/page-start-inventory/:comp_id/:user/:c_house/:check_date/:data_from_search_inventory_page_result',
                component: 'page-start-inventory',
            },
        ],
        dialog: {
            buttonOk: '確認',
            buttonCancel: '取消',
        }
    },
    data: function () {
        return {
            isMounted: 0,
            login: {
                comp_id: '',
                user: '',
                pass: '',
            },
            setting: {
                comp_id: '',
                user: '',
                pass: '',
                c_house: '',
                check_date: '',
            },
        }
    },
    mounted: function () {
        var self = this;
        var app = self.$f7;

        // 顯示 logo (in background)，兩秒後才進到 app
        document.getElementsByTagName('body')[0].style.animation = '2s fadeIn';
        setTimeout(function () {
            // 所有 DOM 渲染完成後才一次顯示出來
            self.isMounted = 1;
        }, 500);

        // 進到 app 後自動 focus on input
        setTimeout(function () {
            document.querySelector('input[name="comp_id"]').focus();
            document.querySelector('input[name="comp_id"]').parentElement.parentElement.parentElement.classList.add("item-input-focused");
        }, 500);

        // 註冊 Android 手機返回鍵的 function
        document.addEventListener('backbutton', onBackKeyDown, false);
        function onBackKeyDown (e) {
            var rootPath = app.views.main.router.history[0];
            var currentPath = app.views.main.router.url;

            if (currentPath === rootPath) {
                // 若在第一個頁面按下 back key 則退出程式
                app.dialog.confirm('確定要結束程式嗎？', function () {
                    if (navigator.app) {
                        navigator.app.exitApp();
                    }
                    else if (navigator.device) {
                        navigator.device.exitApp();
                    }
                    else {
                        app.dialog.alert('無法結束程式。')
                    }
                },
                function () {
                    document.querySelector('input[name="comp_id"]').focus();
                });
            }
            else {
                app.views.main.router.back();
            }
        }

        self.loadSetting();
    },
    methods: {
        loadSetting: function () {
            console.log('Load Setting......');

            var self = this;

            // 設定 local storage
            if (window.localStorage.getItem('comp_id')) {
                self.login.comp_id = window.localStorage.getItem('comp_id');
                self.setting.comp_id = window.localStorage.getItem('comp_id');
            }
            if (window.localStorage.getItem('user')) {
                self.login.user = window.localStorage.getItem('user');
                self.setting.user = window.localStorage.getItem('user');
            }
            if (window.localStorage.getItem('pass')) {
                self.login.pass = window.localStorage.getItem('pass');
                self.setting.pass = window.localStorage.getItem('pass');
            }
            if (window.localStorage.getItem('c_house')) {
                self.setting.c_house = window.localStorage.getItem('c_house');
            }
            if (window.localStorage.getItem('check_date')) {
                self.setting.check_date = window.localStorage.getItem('check_date');
            }

            console.log('Load Setting Success.');
        },
        inputFoolProof: function (self) {
            if (self.comp_id === '') {
                return 'comp_id';
            }
            else if (self.user === '') {
                return 'user';
            }
            else if (self.pass === '') {
                return 'pass';
            }
            else {
                return 'ok';
            }
        },
        tabFocusInput: function (target) {
            if (target == 'login') {
                setTimeout(function () {document.querySelector('input[name="comp_id"]').focus();}, 300);
            }
            else if (target == 'setting') {
                setTimeout(function () {document.querySelector('input[name="s_comp_id"]').focus();}, 300);
            }
        },
        userLogin: function () {
            console.log('User Login......');

            var self = this;
            var app = self.$f7;

            var foolProofResult = self.inputFoolProof(self.login);

            if (foolProofResult !== 'ok') {
                console.log('User Login Failed.');
                app.dialog.alert('請輸入所有資料。', function () {
                    // 關閉提示框後自動 focus on input
                    document.querySelector(`input[name="${foolProofResult}"]`).focus();
                });
            }
            else {
                var params = {
                    op:        'login',
                    comp_id:    self.login.comp_id,
                    user:       self.login.user,
                    pass:       self.login.pass,
                };

                app.request({
                    url: API_SRC,
                    method: 'POST',
                    data: params,
                    beforeSend: function (xhr) {
                        app.preloader.show();
                    },
                    success: function (response, xhr, status) {
                        response = JSON.parse(response);
                        status = response['responseStatus'];
                        if (status == 'OK') {
                            console.log('User Login Success.');
                            app.views.main.router.navigate(`/page-index/${params.comp_id}/${params.user}`);
                        } else {
                            console.log('User Login Failed.');
                            app.dialog.alert('資料輸入錯誤。');
                        }
                    },
                    complete: function (xhr, status) {
                        app.preloader.hide();
                    },
                    error: function (xhr, status) {
                        console.log('User Login Failed.');
                        app.dialog.alert('系統出現非預期錯誤，請聯絡負責人員。');
                    }
                });
            }
        },
        userSetting: function () {
            console.log('User Data Setting......');

            var self = this;
            var app = self.$f7;

            window.localStorage.setItem('comp_id', self.setting.comp_id);
            window.localStorage.setItem('user', self.setting.user);
            window.localStorage.setItem('pass', self.setting.pass);
            window.localStorage.setItem('c_house', self.setting.c_house);
            window.localStorage.setItem('check_date', self.setting.check_date);

            app.dialog.alert('預設資訊設定完成。');
        },
    }
});

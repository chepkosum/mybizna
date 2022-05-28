import accounting from 'accounting';

import fetchComponent from "@/utils/fetchComponent";

const currencyOptions = {
    symbol: '$',
    decimal: '.',
    thousand: ',',
    format: ''
};

const dateFormat = 'd/m/Y';

export default {
    __(value, type) {
        return value;
    },
    currencyUSD(value) {
        return '$' + value;
    },
    toCurrency(value) {
        if (typeof value !== "number") {
            return value;
        }
        var formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 4
        });
        return formatter.format(value);
    },
    uppercase(val) {
        if (typeof val === "string") {
            return val.toUpperCase();
        }
        return val;
    },
    money(val) {
        if (typeof val === "number") {
            return `$${val.toFixed(2)}`;
        }
    },
    formatAmount(val, prefix = false) {
        if (val < 0) {
            return prefix ? `Cr. ${this.moneyFormat(Math.abs(val))}` : `${this.moneyFormat(Math.abs(val))}`;
        }

        return prefix ? `Dr. ${this.moneyFormat(val)}` : `${this.moneyFormat(Math.abs(val))}`;
    },

    formatDBAmount(val, prefix = false) {
        if (val < 0) {
            return `(-) ${this.moneyFormat(Math.abs(val))}`;
        }

        return this.moneyFormat(val);
    },

    showAlert(type, message) {
        this.$swal({
            position: 'center',
            type: type,
            title: message,
            showConfirmButton: false,
            timer: 1500
        });
    },

    getFileName(path) {
        // eslint-disable-next-line no-useless-escape
        return path.replace(/^.*[\\\/]/, '');
    },

    decodeHtml(str) {
        const regex = /^[A-Za-z0-9 ]+$/;

        if (regex.test(str)) {
            return str;
        }

        const txt = document.createElement('textarea');
        txt.innerHTML = str;

        return txt.value;
    },

    moneyFormat(number) {
        return accounting.formatMoney(number, currencyOptions);
    },

    moneyFormatwithDrCr(value) {
        var DrCr = null;

        if (value.indexOf('Dr') > 0) {
            DrCr = 'Dr ';
        } else if (value.indexOf('Dr') === -1) {
            DrCr = 'Cr ';
        }

        const money = accounting.formatMoney(value, currencyOptions);

        return DrCr + money;
    },

    noFulfillLines(lines, selected) {
        let nofillLines = false;

        for (const item of lines) {
            if (!Object.prototype.hasOwnProperty.call(item, selected)) {
                nofillLines = true;
            } else {
                nofillLines = false;
                break;
            }
        }

        return nofillLines;
    },

    formatDate(d) {
        if (!d) {
            return '';
        }

        var date = new Date(d),
            month = date.getMonth() + 1,
            day = date.getDate(),
            year = date.getFullYear();

        if (month.toString().length < 2) {
            month = '0' + month;
        }

        if (day.toString().length < 2) {
            day = '0' + day;
        }

        switch (dateFormat) {
            case 'd/m/Y': // -- 31/12/2020
                return [day, month, year].join('/');

            case 'm/d/Y': // -- 12/31/2020
                return [month, day, year].join('/');

            case 'm-d-Y': // -- 12-31-2020
                return [month, day, year].join('-');

            case 'd-m-Y': // -- 31-12-2020
                return [day, month, year].join('-');

            case 'Y-m-d': // -- 2020-12-31
                return [year, month, day].join('-');

            case 'd.m.Y': // -- 31.12.2020
                return [day, month, year].join('.');

            default:
                return date.toDateString().replace(/^\S+\s/, '');
        }
    },

    fetchComponent(comp_path) {
        return fetchComponent(comp_path);
    },

    formatNumber(n) {

        var ending = ['k', 'm', 'b', 't'];

        var n_str = n.toString();


        if (n_str.length < 4) {
            return n;
        } else {
            //return n_str[0] + ending[Math.floor((n_str.length - 1) / 3) - 1];
            return `${n_str[0]}${n_str[1]!='0'?`.${n_str[1]}`:''}${ending[Math.floor((n_str.length-1)/3)-1]}`;
        }
    }

};

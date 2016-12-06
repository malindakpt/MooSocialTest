var React = require('react');
var Modal = require('react-modal');
var PoupStore = require('../stores/PoupStore');
var CHAT_CONSTANTS = require('../constants/ChatConstants');
var ChatWebAPIUtils = require('../utils/ChatWebAPIUtils');
var __ = require('../utils/ChatMooI18n').i18next;

var ReportWindow = React.createClass({
    handleModalCloseRequest: function () {
        ChatWebAPIUtils.closeReportModal();
    },
    handleSaveClicked: function (e) {

        // Fixing wanring : You tried to return focus to null but it is not in the DOM anymore
        var rId = this.state.data.rId;
        var reason = this.refs.reason.value;

        setTimeout(function () {
            ChatWebAPIUtils.sendRequestReportMesasgeSpam({rId: rId, reason: reason});
        }, 400);
        this.handleModalCloseRequest();
    },
    _onChange: function () {
        this.setState({data: PoupStore.get('report')});
    },
    getInitialState:function() {
        return {data: PoupStore.get('report')};
    },
    componentDidMount:function(){
        PoupStore.addChangeListener(this._onChange);
    },
    componentWillUnmount: function () {
        PoupStore.removeChangeListener(this._onChange);
    },
    render:function(){
        return <Modal
            className="Modal__Bootstrap modal-dialog"
            closeTimeoutMS={150}
            isOpen={this.state.data.isOpen}
            onRequestClose={this.handleModalCloseRequest}
        >
            <div className="modal-content">
                <div className="modal-header">
                    <button type="button" className="close" onClick={this.handleModalCloseRequest}>
                        <span aria-hidden="true">&times;</span>
                        <span className="sr-only">{__.t("popup_title_close")}</span>
                    </button>
                    <h4 className="modal-title">{__.t("popup_title_report")}</h4>
                </div>
                <div className="modal-body">
                    <ul className="list6 list6sm2">
                        <li>
                            <div className="col-md-2">
                                <label>{__.t("popup_reason")}</label>
                            </div>
                            <div className="col-md-10">
                                <textarea ref="reason"></textarea>
                            </div>
                            <div className="clear"></div>
                        </li>
                        <li>
                            <div className="col-md-2">
                                <label>&nbsp;</label>
                            </div>
                            <div className="col-md-10">
                                
                                    <a href="#" onClick={this.handleSaveClicked} className="button" > {__.t("popup_button_report")}</a>

                              
                            </div>
                            <div className="clear"></div>
                        </li>
                    </ul>
                </div>
            </div>
        </Modal>;
    }
});

var AlertWindow = React.createClass({
    handleModalCloseRequest: function () {
        ChatWebAPIUtils.closeAlertModal();
    },
    _onChange: function () {
        this.setState({data: PoupStore.get('alert')});
    },
    getInitialState:function() {
        return {data: PoupStore.get('alert')};
    },
    componentDidMount:function(){
        PoupStore.addChangeListener(this._onChange);
    },
    componentWillUnmount: function () {
        PoupStore.removeChangeListener(this._onChange);
    },
    render:function(){
        return <Modal
            className="Modal__Bootstrap modal-dialog"
            closeTimeoutMS={150}
            isOpen={this.state.data.isOpen}
            onRequestClose={this.handleModalCloseRequest}
        >
            <div className="modal-content">
                <div className="modal-header">
                    <button type="button" className="close" onClick={this.handleModalCloseRequest}>
                        <span aria-hidden="true">&times;</span>
                        <span className="sr-only">{__.t("popup_title_close")}</span>
                    </button>
                    <h4 className="modal-title">{this.state.data.title}</h4>
                </div>
                <div className="modal-body">
                    <ul>
                        <li>
                            <div className="col-md-12">
                                {this.state.data.body}
                            </div>
                            <div className="clear"></div>
                        </li>
                        <li>
                            <div className="clear"></div>
                        </li>
                    </ul>
                </div>
                <div className="modal-footer">
                    <button type="button" className="btn btn-default pull-right"
                            onClick={this.handleModalCloseRequest}>
                        {__.t("popup_button_close")}
                    </button>

                </div>
            </div>
        </Modal>;
    }
});

var AlertYesNoWindow = React.createClass({
    handleModalCloseRequest: function () {
        ChatWebAPIUtils.closeAlertYesNoModal();
    },
    handleSaveCloseRequest: function () {
        this.state.data.callback();
        ChatWebAPIUtils.closeAlertYesNoModal();
    },
    _onChange: function () {
        this.setState({data: PoupStore.get('alertYN')});
    },
    getInitialState:function() {
        return {data: PoupStore.get('alertYN')};
    },
    componentDidMount:function(){
        PoupStore.addChangeListener(this._onChange);
    },
    componentWillUnmount: function () {
        PoupStore.removeChangeListener(this._onChange);
    },
    render:function(){
        return <Modal
            className="Modal__Bootstrap modal-dialog"
            closeTimeoutMS={150}
            isOpen={this.state.data.isOpen}
            onRequestClose={this.handleModalCloseRequest}
        >
            <div className="modal-content">
                <div className="modal-header">
                    <button type="button" className="close" onClick={this.handleModalCloseRequest}>
                        <span aria-hidden="true">&times;</span>
                        <span className="sr-only">{__.t("popup_title_close")}</span>
                    </button>
                    <h4 className="modal-title">{this.state.data.title}</h4>
                </div>
                <div className="modal-body">
                    <ul>
                        <li>
                            <div className="col-md-12">
                                {this.state.data.body}
                            </div>
                            <div className="clear"></div>
                        </li>
                        <li>
                            <div className="col-md-10">

                            </div>

                            <div className="clear"></div>
                        </li>
                    </ul>
                </div>
                <div className="modal-footer">
                    <button type="button" className="btn btn-primary"
                            onClick={this.handleSaveCloseRequest}>
                        {this.state.data.yesButton}
                    </button> &nbsp;&nbsp;&nbsp;
                    <button type="button" className="btn btn-default"
                            onClick={this.handleModalCloseRequest}>
                        {this.state.data.noButton}
                    </button>



                </div>
            </div>
        </Modal>;
    }
});

var PoupWindow = React.createClass({
    render:function(){
        return <div>
            <ReportWindow />
            <AlertWindow />
            <AlertYesNoWindow />
        </div>;
    }
});

module.exports = PoupWindow;
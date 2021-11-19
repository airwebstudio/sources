import React, { Component } from 'react';
import { connect } from 'react-redux'
import { seller, meeting, proposal, feed } from '../../actions'
import { BrowserRouter as Router, Route, withRouter, Link } from 'react-router-dom';
import Header from '../../components/Header';
import Footer from '../../components/Footer';
import {withAuthUser} from 'react-auth-kit';
import draftToHtml from 'draftjs-to-html';
import CrudTable from '../../components/CrudTable';
import AddMeeting from './AddMeeting';

class MeetingList extends Component {

    

    columns = [
        { id: 'name', label: 'name', minWidth: 150, index: true },
        { id: 'description', label: 'Description', minWidth: 180 },            
        { id: 'payment_type', label: 'Payment type', minWidth: 120 },            
        { id: 'type', label: 'Type', minWidth: 40 },            
        { id: 'participants_count', label: 'Participants', minWidth: 50 },            
        { id: 'created_at', label: 'Created', minWidth: 180 },            
        { id: 'starting_at', label: 'Announce Date', minWidth: 190 },            
        { id: 'meeting_dates', label: 'Dates', minWidth: 260 },            
        { id: 'status', label: 'Status', minWidth: 100}, 
    ]           

    filters = [

        {id: 'name', label: 'Name', type: 'text', from_to: false},
        {id: 'type', label: 'Meeting type', type: 'select', 
            type_data: [{id: '1:m', label: "One to Many"}, {id: '1:1', label: 'One to one'}]},

        {id: 'payment_type', label: 'Meeting payment type', type: 'select', 
            type_data: [{id: 'fixed', label: "Fixed"}, {id: 'per_min', label: 'Per minute'}]},

        {id: 'created_at', label: 'Created', type: 'date', from_to: true},

        {id :'status', label: 'Status', type:'select', type_data: [{id: 'finished', label: "Finished"}, {id: 'processing', label: 'Processing now'}, {id: 'not started', label: 'Not started yet'}]}
    ]
        


    
   

    render = () => {
        return (<>
            <Header />

            
           
            <CrudTable filters={this.filters} id_field="hash" title="My meetings" columns={this.columns} type="meeting" update={(page, filters) => this.props.myOwnMeetsAction(page, filters)} />
            <CrudTable filters={this.filters} id_field="hash" title="Particapant of" columns={this.columns} type="meeting" update={(page, filters) => this.props.meParticipantOfAction(page, filters)} />
           
            <AddMeeting />

            <Footer/>
            </>)

    }
}

const mapDispatchToProps = dispatch => ({
    myOwnMeetsAction: (page, filters) => dispatch(meeting['myOwn'](page, filters)),
    meParticipantOfAction: (page, filters) => dispatch(meeting['meParticipantOf'](page, filters)),
    createMeetingAction: (params) => dispatch(meeting['create'](params)),
})

const mapStateToProps = state => {
    return {
        meeting: state['meeting'],
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(withAuthUser(withRouter(MeetingList)));
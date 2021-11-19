import React, { useState, useEffect } from 'react'

import { Link, withRouter } from 'react-router-dom'
import { withAuthUser, withRefreshToken } from 'react-auth-kit'

import { useSelector, useDispatch } from 'react-redux'
import { meeting as meeting_action} from '../../actions'

import Header from '../../components/Header'
import Footer from '../../components/Footer'

import TimePalAvatar from '../../components/TimePalAvatar'


const MeetingInfo = props => {

    const [in_meeting, setInMeeting] = useState(null)

    const meeting = useSelector(
        state => state.meeting.getMeetingInfo.meeting
    )
  
    const dispatch = useDispatch()

    const {hash} = props.match.params

    useEffect(() => {
        dispatch(meeting_action.getMeetingInfo(hash))
        //console.log(meeting)
    }, [])

    const main_fields = [
        {id: 'hash', label: 'Hash'},
        {id: 'owner_string', label: 'Owner'},
        {id: 'name', label: 'Name'},        
        {id: 'description', label: 'Description'},
        {id: 'type', label: 'Type', values: {'1:m': 'One to many', '1:1': 'One to one'}},
        {id: 'payment_type', label: 'Payment type', values: {'fixed': 'Fixed price', 'per_min': 'Price per minute'}},
        {id: 'created_at', label: 'Created'},
        {id: 'starting_at', label: 'Started announced time'},
        {id: 'status', label: 'Meeting status'},
        {id: 'started_at', label: 'Started real time'},
        
        {id: 'finished_at', label: 'Finished real time'},
        {id: 'duration', label: 'Meeting duration'},

        

        {id: 'price_string', label: 'Meeting Price'},
        {id: 'buyer_sum', label: 'Buyer sum amount'},
        {id: 'buyer_reservs_sum', label: 'Buyer reservs sum amount'},
        {id: 'seller_sum', label: 'Seller sum amount'},
       
    ]

    const user_fields = [
        {id: 'name', label: 'Name', show_avatar: true},
        {id: 'email', label: 'Email'},

        {id: 'payment_sum', label: 'Payment sum'},
    ]

    const payment_fields_seller = [
        {id: 'buyer_account_id', label: 'Participant id'},
        {id: 'amount', label: 'Amount'},
        {id: 'description', label: 'Description'},
        {id: 'created_at', label: 'Date'}
    ]


    const outRow = (val, values = false) => (
        values ? (values[val]) :  val
    )

    const outVertTable = (title, fields, data) => (
        <>{data &&
        <>
        { title && (<><h2> {title} </h2>
         <table style={{width: '100%'}}>
            {fields.map((item) => {
                const row = outRow(data[item.id], item.values)
                return(
                    (row !== null && row !== undefined) &&
                    <tr>
                        <td><b>{item.label}</b></td>
                        <td>{row}</td>
                    </tr>
                )
            }
        )}

        </table>
        </>)}
        </>}
        </>
    )

    const outHorTable = (title, fields, data) => (

        <>{(data !== null && data !== undefined && data.length > 0) &&
        <>
        { (title) && (<><h2> {title} </h2>
        <table style={{width: '100%'}}>
            <thead>

            <tr>
            {fields.map((item) => (
                    <th style={{textAlign: "left"}}>{item['label']}</th>                
            )
            )}
            
            </tr>
            </thead>

            <tbody>

                {data.map((_item) => <tr> {fields.map(field => 
                    <td>
                        {field.show_avatar &&  <div style={{float: 'left'}}><TimePalAvatar pathAvatar={_item['avatar_path']} userName={_item['name']} /></div>}
                        {outRow(_item[field.id])}
                        
                    </td>)} </tr>)}

            </tbody>

        </table>
        </>)}
        </>}
        </>
    )


    const participants_to_array = (ps) => ps.map(p => p.user)

    const joinMeeting = async () => {
        setInMeeting(false)
        await dispatch(meeting_action.join(hash))
        
    }     

    

    return (
        <>    
            <Header />

            {(meeting && in_meeting == null) && setInMeeting(meeting.user_in_meeting) }

            {(meeting) ? (
            <div style={{width: 800, margin: "0 auto"}}>
            

            <h1> Meeting info </h1>
            {outVertTable('Main info', main_fields, meeting)}

            {outHorTable('Participants', user_fields, participants_to_array(meeting.participants))}      
            {outHorTable('Buyer transactions', payment_fields_seller, meeting.payments.buyer_transactions)} 
            {outHorTable('Buyer reservs transactions', payment_fields_seller, meeting.payments.buyer_reservs)} 
             
            {outHorTable('Transfers to my wallet', payment_fields_seller, meeting.payments.seller_transactions)}  
            {(meeting.finished_at) ?
                <></> : 

                <>
                {
                (!in_meeting) ?
                    <button onClick={joinMeeting}>Join meeting</button> :
                    <>I am already in meeting</>
                }

                <br/><a target="_blank" href={'/v/' + meeting.hash}>
                    Go to video
                </a>
                </> 

            }
            </div>
            ): <>No data yet</> }
            
                 

            <Footer />
        </>
    )
}

export default withAuthUser(withRouter(withRefreshToken(MeetingInfo)))
import React from 'react'
import { withAuthUser, withRefreshToken } from 'react-auth-kit'
import { Link, withRouter } from 'react-router-dom'

const Payment = props => {

    return 
        <>Wait a moment...
        </>

}

export default withAuthUser(withRouter(withRefreshToken(Payment)))
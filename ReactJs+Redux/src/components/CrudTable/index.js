import React, { useState, useEffect } from 'react'
import ReactPaginate from 'react-paginate'

import  TextField  from '../TextField'


import { Link } from 'react-router-dom'

import styles from '../../styles.scss';


const CrudTable = (props) => {

    useEffect(async () => {
        update()
    }, [])


    const update = async (npage = {selected: 0}) => {


        setPage(npage.selected + 1)

        if (props.itemsData) {
            setitemsData(props.itemsData)

            if (props.page_count) {
                setPageCount(props.page_count)
            }
        }
        else
        if (props.update) {
            const res = await props.update(npage.selected + 1, filters)
            
            if (res.payload.error)
                setError(res.payload.error)

            if (res.payload.data)
                setitemsData(res.payload.data)

            if (res.payload.last_page)
                setPageCount(res.payload.last_page)
        }
            
    }

    const {columns, type, title} = props

    const [filters, setFilters] = useState([])
    const [page, setPage] = useState([])
    const [error, setError] = useState([])
    const [itemsData, setitemsData] = useState([])
    const [page_count, setPageCount] = useState(false)

    const filterChange = (e, ind, id, type, from_to = false) => {
        
        
        if (!filters[ind]) {
            filters[ind] = {id: id, type: type, from_to: (from_to !== false)}
        }

        const value = (type == 'date') ? e : e.target.value

        if (!from_to) {
            filters[ind]['value'] = value
        } 
        else  {

            if (!filters[ind]['value']) {
                filters[ind]['value'] = {}
            }
            filters[ind]['value'][from_to] = value
        }
            

        
        setFilters(filters)


        
        update({ selected: page-1 })
    }

    return (<>

        {error && <h2>{error}</h2> }

        {(itemsData && (itemsData.length && props.filters !== []))  ?
            <>
                {props.filters && (
                <>

                {(title !== '') && <h1>{title}</h1>}

                <form style={{flexDirection: 'row', maxWidth: '1000px'}} > 
                {props.filters.map((item, ind) => (
                    <div style={{margin: '20px'}}>
                        {(item.type == 'select') ?
                            <>
                                <select  style={{width: 'auto'}}                                  
                                    onChange={(e) => filterChange(e, ind, item.id, item.type)}
                                    placeholder={item.label}
                                >   
                                    <option value="" disabled selected>{item.label}</option>
                                    {item.type_data.map((type_item) =>
                                        <option value={type_item.id}>{type_item.label}</option>
                                    )}
                                </select>
                            </>
                            : (item.type == 'text' || item.type == 'date') ?
                                ((!item.from_to) ?
                                <TextField
                                label={item.label}
                                onChange={(e) => filterChange(e, ind, item.id, item.type)}
                                type={item.type}
                                    
                                ></TextField>
                                :
                                
                                (<>
                                <TextField
                                label={item.label + " from"}
                                onChange={(e) => filterChange(e, ind, item.id, item.type, 'from')}
                                type={item.type}
                                InputLabelProps={{ shrink: true }}
                                    
                                ></TextField>

                                <TextField
                                label={item.label + " to"}
                                onChange={(e) => filterChange(e, ind, item.id, item.type, 'to')}
                                type={item.type}
                                InputLabelProps={{ shrink: true }}
                                
                                ></TextField>

                                </>))


                            : ''
                            
                        }
                    </div>
                    ))
                }
                
                </form>
                </>
                )
                }            
                    
                        <table >
                        <thead>
                            <tr>
                            {columns.map((column) => (
                                <th
                                key={column.id}
                                align={column.align}
                                style={{ minWidth: column.minWidth }}
                                >
                                {column.label}
                                </th>
                            ))}
                            </tr>
                        </thead>
                        <tbody>
                            {itemsData.length > 0 && itemsData.map((item, idx) => {
                            return (
                                <tr key={item.id}>
                                {columns.map((column) => {
                                    let value = null;
                                    value = 
                                            column.id === 'update' ? <Link to={(!props.id_field) ? `${type}/${item.id}` : `${type}/${item[props.id_field]}`}>Edit</Link> : 
                                            column.id === 'remove' ? <Button onClick={() => { props.onRemove(item.id) }} variant="contained"color="secondary"  startIcon={<DeleteIcon />}>Delete</Button> : 
                                            column.index ? <Link to={(!props.id_field) ? `${type}/${item.id}` : `${type}/${item[props.id_field]}`}>{ item[column.id] }</Link> : 
                                            item[column.id]
                                    
                                    return (
                                    <td key={column.id + '_' + item.id} align={column.align}>
                                        {column.format && typeof value === 'number' ? column.format(value) : value}
                                    </td>
                                    );
                                })}
                                </tr>
                            );
                            })}
                        </tbody>
                        </table>
                {(page_count) &&
                <div>
                    <ReactPaginate
                        
                        pageCount={page_count}
                        pageRangeDisplayed="5"

                        marginPagesDisplayed="1"
                    
                        onPageChange={update} 

                        containerClassName={styles.pagination} /* as this work same as bootstrap class */
                        subContainerClassName={'pages pagination'} /* as this work same as bootstrap class */
                        activeClassName={'active'} /* as this work same as bootstrap class */
                    
                    />
                </div>
                }
            </> : 'No data yet' 
            }

    </>)
}

export default CrudTable
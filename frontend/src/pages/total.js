import React, { Component } from 'react';
import './../App.css';
import {Form,Row,Col,Table} from 'react-bootstrap';
import {connect} from 'react-redux'
import {
  getAllDatas
} from './../store/Actions/BasicAction';
import axios from 'axios'

class Total extends Component {
  constructor(props) {
      super(props);
      this.state = {
        type:0,
        from:'',
        to:'',
      };
  }
  setDate = (target,e) =>{
    this.setState({
      ...this.state,
      [target]:e.target.value
    });
  }
  componentDidMount() {
    axios.get('basic/list')
        .then(response => {
            this.props.initialData(response.data);
        })
        .catch(function (error){
            console.log(error);
        })
  }
  
  render() {
    const {basic} = this.props;
    const {from,to} = this.state;
    console.log(basic);
    let list =[];
    let nurses =[];
    let nurse_ids =[];
    let dates = [];
    let hour = [];
    let totalhour = [];

    basic.nurses.map((nurse) =>{
      totalhour[nurse._id] = 0;
      nurse.rota.map((rota) =>{
        if (from === '' || new Date(from) <= new Date(rota.date)) {
          if(to === '' || new Date(to) >= new Date(rota.date)){
            hour[nurse._id+rota.date] = rota.hour;
            console.log(hour[nurse._id+rota.date]);
            if(rota.hour !== null && rota.hour !== undefined){
              totalhour[nurse._id] += rota.hour*1;
            }
            let selRota = {_id:nurse._id ,date:rota.date,nurse:nurse.name,hour:rota.hour};
            list = [...list,{...selRota}]
          }
        }

      });
    });

    list.map((value)=>{
      nurses = [...nurses,value.nurse];
      nurse_ids = [...nurse_ids,value._id];
      dates = [...dates,value.date];
    });

    const setNurses = new Set(nurses);
    let nurselist = Array.from(setNurses);
    const setIds = new Set(nurse_ids);
    let idlist = Array.from(setIds);
    const setDates = new Set(dates);
    let datelist = Array.from(setDates);
    datelist.sort();
    
    return (
      <div className="wrapper">
        <h1 className='mt-3'>TOTAL DATA</h1>
        <Row>
          <Col>
            <Row>
              {/* <Col>
                <Form.Group className="mb-3">
                  <Form.Label>Type</Form.Label>
                  <Form.Select aria-label="type select">
                    <option value="1">Nurse</option>
                    <option value="2">Patient</option>
                  </Form.Select>
                </Form.Group>
              </Col> */}
              <Col xs={3}>
              </Col>
              <Col xs={3}>
                <Form.Group className="mb-3">
                  <Form.Label>From</Form.Label>
                  <Form.Control type="date" onChange = {(e) =>this.setDate('from',e)} />
                </Form.Group>
              </Col>
              <Col xs={3}>
                <Form.Group className="mb-3">
                  <Form.Label>To</Form.Label>
                  <Form.Control type="date" value={to}  onChange = {(e) =>this.setDate('to',e)}/>
                </Form.Group>
              </Col>
              <Col xs={3}>
              </Col>
            </Row>
            <div className='p-2'>
              <Table  variant="light" responsive striped>
                <colgroup>
                  <col width={'150px'}></col>
                </colgroup>
                <thead>
                  <tr>
                    <th>Date</th>
                    {
                      nurselist.map((value) =>
                        <th>{value}</th>
                      )
                    }
                  </tr>
                </thead>
                <tbody>
                  {
                    datelist.map((date) =>
                      <tr>
                        <td>{date}</td>
                        {
                          idlist.map((id) =>
                            <th>{hour[id+date]}</th>
                          )
                        }
                      </tr>
                    )
                  }
                    <tr>
                      <td>Total</td>
                        {
                          idlist.map((id) =>
                            <th>{totalhour[id]}</th>
                          )
                        }
                    </tr>
                </tbody>
              </Table>
            </div>
          </Col>
        </Row>
      </div>
    );
  };
}
const mapDispatchToProps = (dispatch) => ({
  initialData:(data) =>dispatch(getAllDatas(data)),
});

const mapStateToProps = (BasicData) => ({
  basic:BasicData.BasicData
});
export default connect(mapStateToProps,mapDispatchToProps)(Total)
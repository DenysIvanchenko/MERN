import React, { Component } from 'react';
import './../App.css';
import axios from 'axios'
import {DropdownButton,Dropdown,Button,Form,Row,Col,FloatingLabel,Modal} from 'react-bootstrap';
import DataTable from 'react-data-table-component';
import {connect} from 'react-redux'

import {
  nUpd,getAllDatas
} from './../store/Actions/BasicAction';

import toastr from 'toastr'
import 'toastr/build/toastr.min.css'

class Rota extends Component {

  constructor(props) {
      super(props);
      this.state = {
        selDate:'',
        selNurse:0,
        selPatient:0,
        open:false,
        modal:{
          _id:'0',
          date:'',
          nurse:'',
          patient:'',
          hour:0,
          service:'',
        },
      };
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

  assign = () =>{
    axios.post('rota/assign',{
      date:this.state.selDate,
      nurse_id:this.state.selNurse,
      patient_id:this.state.selPatient,
    }).then(response => {

      toastr.options = {
        positionClass : 'toast-top-full-width',
        hideDuration: 300,
        timeOut: 3000
      }
      toastr.clear()
      if(response.data.state == 'exist'){
        setTimeout(() => toastr.info(response.data.target+' already assigned!'), 300)
      }else{
        setTimeout(() => toastr.info('Data Assigned!'), 300)
        this.props.assign(response.data.data);
      }
    })
    .catch(function (error){
        console.log(error);
    })  
  }
  reportModal = (open,data) =>{
    console.log(data);
    if(open == true){
      this.setState({
          ...this.state,
          open:open,
          modal:{
            ...this.state.modal,
            ...data
          }
      });
    }else{
      this.setState({
          open:open,
          modal:{
            ...this.state.modal,
            hour:0,
            service:'',
          }
      });
    }
  }
  reportModalChange = (target,e) =>{
    console.log(target);
    this.setState({
      ...this.state,
      modal:{
        ...this.state.modal,
        [target]:e.target.value
      }
    });
  }

  reportConfirm = () =>{
    const _self = this;
    this.setState({
      ...this.state,
      open:false
    });
    
    axios.post('rota/report',{
      ...this.state.modal
    })
    .then(function (response) {
      console.log(response.data);
      _self.props.assign(response.data);
    })
    .catch(function (error) {
      console.log(error);
    });
  };

  remove = (data) =>{
    console.log(data);
    const _self = this;
    axios.post('rota/delete',{
      ...data
    })
    .then(function (response) {
      console.log(response.data);
      _self.props.assign(response.data);
    })
    .catch(function (error) {
      console.log(error);
    });

  }
  onChangeSelect = (target,e) =>{

    this.setState({
      ...this.state,
      [target]:e.target.value
    });
  }

  render() {
    const {basic} = this.props;
    const {selNurse,selPatient,selDate,open,modal} = this.state;

    let rotas =[];
    basic.nurses.map((nurse) =>{
      nurse.rota.map((rota) =>{
        basic.patients.map((patient) =>{
          if(rota.patient_id == patient._id){
            let selRota = {_id:nurse._id ,date:rota.date,nurse:nurse.name,patient:patient.name,service:rota.service,hour:rota.hour};
            rotas = [...rotas,{...selRota}]
          }
        });
      })
    });

    const rotaColumns = [
      {
        name: "Date",
        center:true,
        wrap:true,
        sortable: true,
        selector: (row) => row.date,
      },
      {
        name: "Nurse",
        center:true,
        sortable: true,
        wrap:true,
        selector: (row) => row.nurse,
      },
      {
        name: "Patient",
        center:true,
        sortable: true,
        wrap:true,
        selector: (row) => row.patient,
      },
      {
        name: "Hour",
        center:true,
        wrap:true,
        selector: (row) => row.hour,
      },
      {
        name: "Service",
        center:true,
        wrap:true,
        selector: (row) => row.service,
      },
      {
        name: "Action",
        center:true,
        wrap:true,
        sortable: false,
        cell: (d) => [
          <DropdownButton key={d._id} id="dropdown-basic-button" title="Action" style={{width:'100px'}}>
            <Dropdown.Item href="#" onClick={() => this.reportModal(true,d)}>report</Dropdown.Item>
            <Dropdown.Item href= "#" onClick={() => this.remove(d)}>delete</Dropdown.Item>
          </DropdownButton>
        ]
      }
    ];

    return (
      <div className="wrapper">
        <h1 className='mt-3'>ROTA SYSTEM</h1>
        <Row>
          <Col>
            <Row>
              <Col>
                <Form.Group className="m-3">
                  <Form.Control type="date" value={selDate} onChange = {(e) =>this.onChangeSelect('selDate',e)}/>
                </Form.Group>
              </Col>
              <Col>
                <Form.Group className="m-3">
                  <Form.Select aria-label="nurse select" value={selNurse} onChange = {(e) =>this.onChangeSelect('selNurse',e)} >
                    <option value='0'>Select Nurse</option>
                    {
                      basic.nurses.map((value,index) =>{
                        return <option key={index} value={value._id}>{value.name}</option>
                      })  
                    }
                  </Form.Select>
                </Form.Group>
              </Col>
              <Col>
                <Form.Group className="m-3">
                  <Form.Select aria-label="patient select" value={selPatient} onChange = {(e) =>this.onChangeSelect('selPatient',e)}>
                    <option value="0" >Select Patient</option>
                    {
                      basic.patients.map((value,index) =>{
                        return <option key = {index} value={value._id}>{value.name}</option>
                      })  
                    }
                  </Form.Select>
                </Form.Group>
              </Col>
              <Col>
                <div className="d-grid gap-2">
                  <Button variant="info" className='m-3 float-right' onClick={()=>this.assign()}>Assign</Button>
                </div>
              </Col>
            </Row>
            <div className='p-2'>
              <DataTable 
                columns={rotaColumns} 
                data={rotas}
                fixedHeader
                fixedHeaderScrollHeight={'300px'}
                pagination />
            </div>
          </Col>
        </Row>
        <Modal show={open}
          size="lg"
          aria-labelledby="contained-modal-title-vcenter"
          centered
          onHide={() => this.reportModal(false)}>
          <Modal.Header closeButton>
            <Modal.Title>Report</Modal.Title>
          </Modal.Header>
          <Modal.Body>
            <Row>
              <Col xs={3}>
                <FloatingLabel 
                  controlId="DateInput" 
                  label="Assign Date"
                  className="mb-3"
                >
                  <Form.Control type="date" value={modal.date}  placeholder="Assign Date" disabled />
                </FloatingLabel>
              </Col>
              <Col>
                <FloatingLabel
                  controlId="NurseInput"
                  label="Nurse"
                  className="mb-3"
                >
                  <Form.Control type="text" value={modal.nurse} placeholder="Nurse" disabled />
                </FloatingLabel>
              </Col>
              <Col>
                <FloatingLabel 
                  controlId="patientInput" 
                  label="Patient"
                  className="mb-3"
                >
                  <Form.Control type="text" value={modal.patient} placeholder="Patient" disabled/>
                </FloatingLabel>
              </Col>
            </Row>
            <Row>
              <Col>
                <FloatingLabel
                  controlId="HourInput"
                  label="Hour"
                  className="mb-3"
                >
                  <Form.Control type="number" value={modal.hour} step={0} onInput={(e) => this.reportModalChange('hour',e)} placeholder="Hour" />
                </FloatingLabel>
              </Col>
              <Col>
                <FloatingLabel 
                  controlId="serviceInput" 
                  label="Service"
                  className="mb-3"
                >
                  <Form.Control type="text" value={modal.service} onChange={(e) => this.reportModalChange('service',e)} placeholder="Service" />
                </FloatingLabel>
              </Col>
            </Row>
          </Modal.Body>
          <Modal.Footer>
            <Button variant="secondary" onClick={() => this.reportModal(false)}>
              Close
            </Button>
            <Button variant="primary" onClick={() => this.reportConfirm()}>
              Save
            </Button>
          </Modal.Footer>
        </Modal>  
      </div>
    );
  };
}

const mapDispatchToProps = (dispatch) => ({
  assign:(data) =>dispatch(nUpd(data)),
  initialData:(data) =>dispatch(getAllDatas(data)),
});

const mapStateToProps = (BasicData) => ({
  basic:BasicData.BasicData
});
export default connect(mapStateToProps,mapDispatchToProps)(Rota)

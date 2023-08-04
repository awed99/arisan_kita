import React from 'react';
import { useState, useEffect, useRef } from 'react';
import { Toastr, ToastrRef, ToastrProps } from '@paljs/ui/Toastr';
import { Button } from '@paljs/ui/Button';
import Select from '@paljs/ui/Select';
import Spinner from '@paljs/ui/Spinner';
import Progress from '@paljs/ui/ProgressBar';
import Layout from 'Layouts';
import Row from '@paljs/ui/Row';
import Col from '@paljs/ui/Col';
import { InputGroup } from '@paljs/ui/Input';
import { Card, CardBody, CardHeader, CardFooter } from '@paljs/ui/Card';
import { List, ListItem } from '@paljs/ui/List';
import User from '@paljs/ui/User';
import styled from 'styled-components';

import { validateEmail, checkPassword } from 'src/helpers/hooks';
import { size, filter } from 'lodash';
import CryptoJS from "crypto-js"
import Store from 'store'

export const SelectStyled = styled(Select)`
  margin-bottom: 1rem;
`;

const Input = styled(InputGroup)`
  margin-bottom: 1rem;
`;

const Profile = () => {
  const toastrRef = useRef(null);
  const [loading, setLoading] = useState(false);
  const [banks, setBanks] = useState([]);
  // const [title, setTitle] = useState('Success');
  // const [message, setMessage] = useState('Check your email for activation.');

  const [data, setData] = useState({
    position: 'topEnd',
    status: 'Success',
    duration: 3000,
    hasIcon: true,
    destroyByClick: true,
    preventDuplicates: true,
  });

  const [isError, setIsError] = useState(true)
  const [isError2, setIsError2] = useState(true)
  const [progress, setProgress] = useState(0)
  const [progress2, setProgress2] = useState(0)
  const [values, setValues] = useState({
    full_name: '',
    username: '',
    phone: '',
    email: '',
    password: '',
    confirmPassword: ''
  })
  const [values2, setValues2] = useState({
    user_bank_name: '',
    user_bank_account_number: '',
    user_bank_account_name: '',
  })

  const onChangeHandle = (name, value) => {
    const newData = { ...data };
    newData[name] = value;
    setData(newData);
  };

  useEffect(() => {
    let _isError = 0
    if (size(values?.full_name) < 3) {
      _isError++
    }
    if (size(values?.username) < 6) {
      _isError++
    }
    if (size(values?.phone) < 10) {
      _isError++
    }
    if (!validateEmail(values?.email)) {
      _isError++
    }
    if ((size(values?.password) > 0) && checkPassword(values?.password)?.error === 'Weak!') {
      _isError++
    }
    if ((size(values?.password) > 0) && checkPassword(values?.confirmPassword)?.error === 'Weak!' || values?.confirmPassword !== values?.password) {
      _isError++
    }
    
    if ((size(values?.password) > 0)) {
        setProgress((5-_isError)/5*100)
    } else {
        setProgress((3-_isError)/3*100)
    }

    if (_isError > 0) {
      setIsError(true)
    } else {
      setIsError(false)
    }
    // console.log('values:', values);
  }, [values])

  useEffect(() => {
    let _isError = 0
    if (size(values2?.user_bank_name) < 3) {
      _isError++
    }
    if (size(values2?.user_bank_account_number) < 6) {
      _isError++
    }
    // if (size(values2?.user_bank_account_name) < 3) {
    //   _isError++
    // }

    setProgress2((2-_isError)/2*100)
    if (_isError > 0) {
      setIsError2(true)
    } else {
      setIsError2(false)
    }
    // console.log('values2:', values2);
  }, [values2])

  const handleChange = prop => event => {
    setValues({ ...values, [prop]: event.target.value })
  }

  const handleChange2 = prop => event => {
    setValues2({ ...values2, [prop]: event.target.value })
  }

  const handleChange2a = prop => event => {
    console.log('event:', event)
    setValues2({ ...values2, [prop]: event.value })
  }

  const onCheckbox = prop => value => {
    setValues({ ...values, [prop]: value })
  };

  const getData = () => {
    setLoading(true)
    const _data = new FormData()
    fetch(`${process.env.NEXT_PUBLIC_BE_API}user/get_data`,
    {
      method: 'POST',
      headers: {
          'Authorization': Store.get('token')
      },
      body: _data
    })
    .then((res) => res.json())
    .then((res) => {
        setValues({
            full_name: res?.data?.full_name,
            username: res?.data?.username,
            phone: res?.data?.telp,
            email: res?.data?.email,
            password: '',
            confirmPassword: ''
        })
        setValues2({
            user_bank_name: res?.data?.user_bank_name,
            user_bank_account_number: res?.data?.user_bank_account_number,
            user_bank_account_name: res?.data?.user_bank_account_name,
        })
        setBanks(res?.banks)
        setLoading(false)
    })
    .catch(() => setLoading(false))
  }

  const handleSubmit = (event) => {
    event.preventDefault()
    
    setLoading(true)
    const _data = new FormData()
    _data.append('full_name', values?.full_name.trim())
    _data.append('username', values?.username.trim().toLocaleLowerCase())
    _data.append('telp', values?.phone.trim().replace(/\s/g, '').replace(/^08+/, '+628'))
    _data.append('password', values?.password.trim())
    fetch(`${process.env.NEXT_PUBLIC_BE_API}user/update`,
    {
      method: 'POST',
      headers: {
          'Authorization': Store.get('token')
      },
      body: _data
    })
    .then((res) => res.json())
    .then((res) => {
        let _status0 = (res?.status === '000') ? 'Success' : 'Error'
        let _status1 = (res?.status === '000') ? 'Success' : 'Danger'
        toastrRef.current?.add(res?.message, _status0, {
            position: 'topEnd',
            status: _status1,
            duration: 3000,
            hasIcon: true,
            destroyByClick: true,
            preventDuplicates: false,
        })

      setLoading(false)
    })
    .catch(() => setLoading(false))
  }

  const handleSubmit2 = (event) => {
    event.preventDefault()
    
    setLoading(true)
    const _data = new FormData()
    _data.append('user_bank_name', values2?.user_bank_name.trim().toLocaleLowerCase())
    _data.append('user_bank_account_number', values2?.user_bank_account_number.trim().toLocaleLowerCase())
    fetch(`${process.env.NEXT_PUBLIC_BE_API}user/get_bank_account`,
    {
      method: 'POST',
      headers: {
          'Authorization': Store.get('token')
      },
      body: _data
    })
    .then((res) => res.json())
    .then((res) => {
        let _status0 = (res?.status === '000') ? 'Success' : 'Error'
        let _status1 = (res?.status === '000') ? 'Success' : 'Danger'
        toastrRef.current?.add(res?.message, _status0, {
            position: 'topEnd',
            status: _status1,
            duration: 3000,
            hasIcon: true,
            destroyByClick: true,
            preventDuplicates: false,
        })
        setValues2({
            user_bank_name: res?.data?.user_bank_name,
            user_bank_account_number: res?.data?.user_bank_account_number,
            user_bank_account_name: res?.data?.user_bank_account_name,
        })

      setLoading(false)
    })
    .catch(() => setLoading(false))
  }

  useEffect(() => {
    getData()
  }, [])

  return <Layout title="Home">
    <Row>
      <Col breakPoint={{ xs: 12, md: 6 }}>
        <Card status="Info">
          <CardHeader>My Profile</CardHeader>
          <CardBody sx={{p:10}}>

            <form onSubmit={e => handleSubmit(e)}>
                <Input fullWidth status={(size(values?.full_name) < 3) ? 'Warning' : 'Success'}>
                    <input value={values?.full_name} autoFocus={true} onChange={handleChange('full_name')} type="text" placeholder="Full Name" />
                </Input>
                <Input fullWidth status={(size(values?.username) < 6) ? 'Warning' : 'Success'}>
                    <input value={values?.username} autoFocus={true} onChange={handleChange('username')} type="text" placeholder="Username" />
                </Input>
                <Input fullWidth status={(size(values?.phone) < 10) ? 'Warning' : 'Success'}>
                    <input value={values?.phone} onChange={handleChange('phone')} type="text" placeholder="Phone Number 0812 3456 7890" />
                </Input>
                <Input fullWidth status={!validateEmail(values?.email) ? 'Warning' : 'Success'}>
                    <input value={values?.email} type="email" onChange={handleChange('email')} placeholder="Email Address" disabled={true} />
                </Input>
                <br/>
                <p>Fill <b>Password</b> if you want to change password.</p>
                <Input fullWidth status={((size(values?.password) > 0) && checkPassword(values?.password)?.error === 'Weak!') ? 'Warning' : 'Success'}>
                    <input value={values?.password} type="password" onChange={handleChange('password')} placeholder="Password" />
                    {/* <span>{checkPassword(values?.password)?.error || checkPassword(values?.password)?.valid}</span> */}
                </Input>
                <Input fullWidth status={((size(values?.password) > 0) && checkPassword(values?.confirmPassword)?.error === 'Weak!' || values?.confirmPassword !== values?.password) ? 'Warning' : 'Success'}>
                    <input value={values?.confirmPassword} type="password" onChange={handleChange('confirmPassword')} placeholder="Confirm Password" />
                </Input>
                <Button status="Success" type="submit" shape="SemiRound" fullWidth
                disabled={isError || loading}>
                    Update
                    {loading && <Spinner size="Large" status="Danger" />}
                </Button>
                
                <Progress style={{ marginBottom: '1rem', marginTop: '1rem' }} value={progress} status="Info">
                    Valid
                </Progress>
            </form>

            <Toastr ref={toastrRef} />
            
          </CardBody>
          {/* <CardFooter>Footer</CardFooter> */}
        </Card>
      </Col>

      <Col breakPoint={{ xs: 12, md: 4 }}>
        <Card status="Warning">
          <CardHeader>Bank Account</CardHeader>
          <CardBody sx={{p:10}}>

            <form onSubmit={e => handleSubmit2(e)}>
                <SelectStyled appearance="outline" value={filter(banks, ['value', values2?.user_bank_name])[0]} onChange={handleChange2a('user_bank_name')} options={banks} status={(size(values2?.user_bank_name) < 3) ? 'Warning' : 'Success'} placeholder="Bank Name" />
                {/* <Input fullWidth status={(size(values2?.user_bank_name) < 3) ? 'Warning' : 'Success'}>
                    <input value={values2?.user_bank_name} autoFocus={true} onChange={handleChange2('user_bank_name')} type="text" placeholder="Bank Name" />
                </Input> */}
                <Input fullWidth status={(size(values2?.user_bank_account_number) < 6) ? 'Warning' : 'Success'}>
                    <input value={values2?.user_bank_account_number} autoFocus={true} onChange={handleChange2('user_bank_account_number')} type="text" placeholder="Bank Account Number" />
                </Input>
                <Input fullWidth status={size(values2?.user_bank_account_name) < 3 ? 'Warning' : 'Success'}>
                    <input value={values2?.user_bank_account_name} onChange={handleChange2('user_bank_account_name')} placeholder="Bank Account Name" disabled={true} />
                </Input>
                <br/>
                <Button status="Warning" type="submit" shape="SemiRound" fullWidth
                disabled={isError2 || loading}
                >
                    Update
                    {loading && <Spinner size="Large" status="Danger" />}
                </Button>
                
                <Progress style={{ marginBottom: '1rem', marginTop: '1rem' }} value={progress2} status="Info">
                    Valid
                </Progress>
            </form>

            <Toastr ref={toastrRef} />
            
          </CardBody>
          {/* <CardFooter>Footer</CardFooter> */}
        </Card>
      </Col>
    </Row>
  </Layout>;
};
export default Profile;

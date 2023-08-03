import { useState, useEffect, useRef } from 'react';
import { Toastr, ToastrRef, ToastrProps } from '@paljs/ui/Toastr';
import { Button } from '@paljs/ui/Button';
import { InputGroup } from '@paljs/ui/Input';
import { Checkbox } from '@paljs/ui/Checkbox';
import Spinner from '@paljs/ui/Spinner';
import Progress from '@paljs/ui/ProgressBar';
import React from 'react';
import styled from 'styled-components';
import Link from 'next/link';

import Auth from 'components/Auth';
import Layout from 'Layouts';
import Socials from 'components/Auth/Socials';

import { validateEmail, checkPassword } from 'src/helpers/hooks';
import { size } from 'lodash';
import CryptoJS from "crypto-js"
import Store from 'store'

const Input = styled(InputGroup)`
  margin-bottom: 2rem;
`;

export default function Register() {

  const toastrRef = useRef<ToastrRef>(null);
  const [loading, setLoading] = useState(false);
  const [count, setCount] = useState(1);
  // const [title, setTitle] = useState('Success');
  // const [message, setMessage] = useState('Check your email for activation.');

  const [data, setData] = useState<ToastrProps>({
    position: 'topEnd',
    status: 'Success',
    duration: 3000,
    hasIcon: true,
    destroyByClick: true,
    preventDuplicates: true,
  });

  const [isError, setIsError] = useState(false)
  const [progress, setProgress] = useState(0)
  const [values, setValues] = useState({
    username: '',
    phone: '',
    email: '',
    password: '',
    confirmPassword: '',
    agree: false
  })

  const onChangeHandle = (name: keyof ToastrProps, value: any) => {
    const newData = { ...data };
    newData[name] = value;
    setData(newData);
  };

  useEffect(() => {
    let _isError = 0
    if (size(values?.username) < 6) {
      _isError++
    }
    if (size(values?.phone) < 10) {
      _isError++
    }
    if (!validateEmail(values?.email)) {
      _isError++
    }
    if (checkPassword(values?.password)?.error === 'Weak!') {
      _isError++
    }
    if (checkPassword(values?.confirmPassword)?.error === 'Weak!' || values?.confirmPassword !== values?.password) {
      _isError++
    }
    if (!values?.agree) {
      _isError++
    }

    setProgress((6-_isError)/6*100)
    if (_isError > 0) {
      setIsError(true)
    } else {
      setIsError(false)
    }
    // console.log('values:', values);
  }, [values])

  const handleChange = prop => event => {
    setValues({ ...values, [prop]: event.target.value })
  }

  const onCheckbox = prop => value => {
    setValues({ ...values, [prop]: value })
  };

  const handleSubmit = (event:any) => {
    event.preventDefault()
    
    setLoading(true)
    const _data = new FormData()
    _data.append('username', values?.username.trim().toLocaleLowerCase())
    _data.append('telp', values?.phone.trim().replace(/\s/g, '').replace(/^08+/, '+628'))
    _data.append('email', values?.email.trim().toLocaleLowerCase())
    _data.append('password', values?.password.trim())
    fetch(`${process.env.NEXT_PUBLIC_BE_API}user/register`,
    {
      method: 'POST',
      body: _data
    })
    .then((res) => res.json())
    .then((res) => {
      if (res?.status === '000') { 
        toastrRef.current?.add(res?.message, 'Success', {
          position: 'topEnd',
          status: 'Success',
          duration: 3000,
          hasIcon: true,
          destroyByClick: true,
          preventDuplicates: false,
        })
        setValues({
          username: '',
          phone: '',
          email: '',
          password: '',
          confirmPassword: '',
          agree: false
        })
        setTimeout(() => window.location.href='/auth/login', 3000)
      } else {
        toastrRef.current?.add(res?.message, 'Danger', {
          position: 'topEnd',
          status: 'Danger',
          duration: 3000,
          hasIcon: true,
          destroyByClick: true,
          preventDuplicates: false,
        })
      }

      setTimeout(() => setLoading(false), 3000)
    })
    .catch(() => setLoading(false))
  }

  useEffect(() => {
    if (
      Store.get('auth-user') &&
      CryptoJS.AES.decrypt(`${Store.get('auth-user')}`, `${process.env.NEXT_PUBLIC_SECRET_KEY}`).toString(CryptoJS.enc.Utf8)
    ) {
      window.location.href='/'
    }

  }, [])

  return (
    <Layout title="Register">
      <Auth title="Buat Akun Baru">
        <form onSubmit={e => handleSubmit(e)}>
          <Input fullWidth status={(size(values?.username) < 6) ? 'Warning' : 'Success'}>
            <input autoFocus={true} onChange={handleChange('username')} type="text" placeholder="Username" />
          </Input>
          <Input fullWidth status={(size(values?.phone) < 10) ? 'Warning' : 'Success'}>
            <input onChange={handleChange('phone')} type="text" placeholder="Phone Number 0812 3456 7890" />
          </Input>
          <Input fullWidth status={!validateEmail(values?.email) ? 'Warning' : 'Success'}>
            <input type="email" onChange={handleChange('email')} placeholder="Email Address" />
          </Input>
          <Input fullWidth status={(checkPassword(values?.password)?.error === 'Weak!') ? 'Warning' : 'Success'}>
            <input type="password" onChange={handleChange('password')} placeholder="Password" />
            {/* <span>{checkPassword(values?.password)?.error || checkPassword(values?.password)?.valid}</span> */}
          </Input>
          <Input fullWidth status={(checkPassword(values?.confirmPassword)?.error === 'Weak!' || values?.confirmPassword !== values?.password) ? 'Warning' : 'Success'}>
            <input type="password" onChange={handleChange('confirmPassword')} placeholder="Confirm Password" />
          </Input>
          <Checkbox checked={values?.agree} onChange={onCheckbox('agree')}>
            Agree to{' '}
            <Link href="/">
              <a>Terms & Conditions</a>
            </Link>
          </Checkbox>
          <Button status="Success" type="submit" shape="SemiRound" fullWidth
          disabled={isError || loading}>
            Register
            {loading && <Spinner size="Large" status="Danger" />}
          </Button>
          
          <Progress style={{ marginBottom: '1rem' }} value={progress} status="Info">
            Valid
          </Progress>
        </form>
        <Socials />
        <p>
          Sudah mendaftar?{' '}
          <Link href="/auth/login">
            <a>Log In</a>
          </Link>
        </p>
      </Auth>
      <Toastr ref={toastrRef} />
    </Layout>
  );
}

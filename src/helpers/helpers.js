import { keys, map } from 'lodash'

export const dataFetcherQuery = async (url, queryName, result, variables=false, authorization='') => {
  let _var = ''
  let _loop = 1

  if (variables) {
    if (typeof variables === 'object' ) {
      _var = '('
      map(keys(variables), item => {
        _var += `${item}: "${variables[item]}"`
        if (_loop > 1) _var += ','
        _loop++
      })
      _var += ')'
    } else {
      _var = variables
    }
  }

  return await fetch(url, {
    method: 'POST',
    headers: {'Content-Type': 'application/json', 'Authorization': authorization},
    body: JSON.stringify({
      query: `{
        ${queryName}${_var}${result}
      }`
    })
  }).then((res) => res.json())
}

export const dataFetcherQuery2 = async (url, queryName, result, variables=false, authorization='') => {
  let _var = ''
  let _loop = 1

  if (variables) {
    if (typeof variables === 'object' ) {
      _var = '('
      map(keys(variables), item => {
        _var += `${item}: "${variables[item]}"`
        if (_loop > 1) _var += ','
        _loop++
      })
      _var += ')'
    } else {
      _var = variables
    }
  }

  return await fetch(url, {
    method: 'POST',
    headers: {'Content-Type': 'application/json', 'Authorization': authorization},
    body: JSON.stringify({
      query: `{
        ${queryName}${_var}${result}
      }`
    })
  }).then((res) => res.json())
}

export const dataFetcherMutation2 = async (url, queryName, result, variables=false, authorization='') => {
  let _var = ''
  let _loop = 1

  if (variables) {
    if (typeof variables === 'object' ) {
      _var = '('
      map(keys(variables), item => {
        _var += `${item}: "${variables[item]}"`
        if (_loop > 1) _var += ','
        _loop++
      })
      _var += ')'
    } else {
      _var = variables
    }
  }

  return await fetch(url, {
    method: 'POST',
    headers: {'Content-Type': 'application/json', 'Authorization': authorization},
    body: JSON.stringify({
      query: `mutation {
        ${queryName}${_var}${result}
      }`
    })
  }).then((res) => res.json())
}

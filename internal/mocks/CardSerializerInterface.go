// Code generated by mockery v1.0.0. DO NOT EDIT.
package mocks

import (
	model "github.com/Confialink/wallet-accounts/internal/modules/card/model"
	mock "github.com/stretchr/testify/mock"
)

// CardSerializerInterface is an autogenerated mock type for the CardSerializerInterface type
type CardSerializerInterface struct {
	mock.Mock
}

// Deserialize provides a mock function with given fields: data, fields
func (_m *CardSerializerInterface) Deserialize(data *[]byte, fields []string) (*model.SerializedCard, error) {
	ret := _m.Called(data, fields)

	var r0 *model.SerializedCard
	if rf, ok := ret.Get(0).(func(*[]byte, []string) *model.SerializedCard); ok {
		r0 = rf(data, fields)
	} else {
		if ret.Get(0) != nil {
			r0 = ret.Get(0).(*model.SerializedCard)
		}
	}

	var r1 error
	if rf, ok := ret.Get(1).(func(*[]byte, []string) error); ok {
		r1 = rf(data, fields)
	} else {
		r1 = ret.Error(1)
	}

	return r0, r1
}

// DeserializeFields provides a mock function with given fields: data, fields
func (_m *CardSerializerInterface) DeserializeFields(data *[]byte, fields []string) (map[string]interface{}, error) {
	ret := _m.Called(data, fields)

	var r0 map[string]interface{}
	if rf, ok := ret.Get(0).(func(*[]byte, []string) map[string]interface{}); ok {
		r0 = rf(data, fields)
	} else {
		if ret.Get(0) != nil {
			r0 = ret.Get(0).(map[string]interface{})
		}
	}

	var r1 error
	if rf, ok := ret.Get(1).(func(*[]byte, []string) error); ok {
		r1 = rf(data, fields)
	} else {
		r1 = ret.Error(1)
	}

	return r0, r1
}

// Serialize provides a mock function with given fields: card, fields
func (_m *CardSerializerInterface) Serialize(card *model.Card, fields []interface{}) map[string]interface{} {
	ret := _m.Called(card, fields)

	var r0 map[string]interface{}
	if rf, ok := ret.Get(0).(func(*model.Card, []interface{}) map[string]interface{}); ok {
		r0 = rf(card, fields)
	} else {
		if ret.Get(0) != nil {
			r0 = ret.Get(0).(map[string]interface{})
		}
	}

	return r0
}
declare module Mock.Bar.Also {
    class RunningOutOfNames {
    }
}
declare module Mock.Bar {
    class BarTest {
        constructor(bar: Mock.Bar.Test.DeepTest);
    }
}
declare module Mock.Bar.Test {
    class DeepTest {
    }
}
declare var foo: Mock.Bar.BarTest;

